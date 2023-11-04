<?php

namespace App\Controller;

use App\Entity\GuideKeeper;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Service\AppState;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

/**
 * User Controller is for anything that creates, modifies, or removes user accounts or user user controls, lets someone login or logout, or lets someone access something that only makes sense if you're logged in but not playing.
 */
class UserController extends AbstractController {
	private EmailVerifier $emailVerifier;
	private TranslatorInterface $trans;
	private AppState $app;

	public function __construct(AppState $app, EmailVerifier $emailVerifier, TranslatorInterface $trans) {
		$this->app = $app;
		$this->emailVerifier = $emailVerifier;
		$this->trans = $trans;
	}

	#[Route(path: '/login', name: 'user_login')]
	public function login(AuthenticationUtils $authenticationUtils): Response {
		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();

		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();

		return $this->render('user/login.html.twig', ['last_username' => $lastUsername, 'error' => $error,]);
	}

	#[Route(path: '/logout', name: 'user_logout')]
	public function logout(): void {
		throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
	}

	#[Route('/register', name: 'user_register')]
	public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response {
		$user = new User();
		$form = $this->createForm(RegistrationFormType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			// encode the plain password
			$user->setPassword($userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData()));

			$entityManager->persist($user);
			$user->setCreated(new DateTime('now'));
			$entityManager->flush();

			// generate a signed url and email it to the user
			#TODO: Run this through the tranlsator system.
			$this->emailVerifier->sendEmailConfirmation(
				'user_verify_email',
				$user, (new TemplatedEmail())
					->from(new Address($_ENV['FROM_EMAIL'], $_ENV['FROM_NAME']))
					->to($user->getEmail())
					->subject('Please Confirm your Email')
					->htmlTemplate('user/confirmation_email.html.twig')
			);
			// do anything else you need here, like send an email

			$this->addFlash('notice', $this->trans->trans('user.register.emailSent', [], 'security'));
			return $this->redirectToRoute('public_index');
		}

		return $this->render('user/register.html.twig', ['registrationForm' => $form->createView(),]);
	}

	#[Route ('/user/reset', name:'user_reset')]
	public function reset(AppState $app, EntityManagerInterface $em, MailManager $mail, TranslatorInterface $trans, Request $request, UserPasswordHasherInterface $passwordHasher, string $token = '0', string $email = '0'): RedirectResponse|Response {
		if ($token == '0') {
			$form = $this->createForm(RequestResetFormType::class);
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$data = $form->getData();
				$user = $em->getRepository(User::class)->findOneByEmail($data['text']);
				if (!$user) {
					$user = $em->getRepository(User::class)->findOneByUsername($data['text']);
				}
				if ($user) {
					$user->setResetToken($app->generateAndCheckToken(64, 'User', 'resetToken'));
					$em->flush();
					$link = $this->generateUrl('user_reset', ['token' => $user->getResetToken(), 'email'=>$user->getEmail()], UrlGeneratorInterface::ABSOLUTE_URL);
					$text = $trans->trans(
						'user.reset.email.text', [
						'%sitename%' => $_ENV['SITE_NAME'],
						'%link%' => $link,
						'%adminemail%' => $_ENV['ADMIN_EMAIL']
					], 'security');
					$subject = $trans->trans('user.reset.email.subject', ['%sitename%' => $_ENV['SITE_NAME']], 'security');

					$mail->sendEmail($user->getEmail(), $subject, $text);
					$this->addFlash('notice', $trans->trans('user.reset.flash.requested', [], 'security'));
				}
				return new RedirectResponse($this->generateUrl('public_index'));
			}
			return $this->render('user/reset.html.twig', [
				'form' => $form->createView(),
			]);
		} else {
			$user = $em->getRepository(User::class)->findOneBy(['reset_token' => $token, 'email' => $email]);
			if ($user) {
				$form = $this->createForm(ResetPasswordFormType::class);
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) {
					$user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
					$user->setLastPassword(new \DateTime('now'));
					$user->unsetResetToken();
					$user->unsetResetTime();
					$em->flush();

					$this->addFlash('notice', $trans->trans('user.reset.flash.completed', [], 'security'));
					return new RedirectResponse($this->generateUrl('public_index'));
				}
				return $this->render('user/reset.html.twig', [
					'form' => $form->createView(),
				]);
			} else {
				$app->logSecurityViolation($request->getClientIP(), 'core_reset', $this->getUser(), 'bad reset');
				return new RedirectResponse($this->generateUrl('public_index'));
			}
		}
	}

	#[Route('/user/verify', name: 'user_verify_email')]
	public function verifyUserEmail(Request $request, EntityManagerInterface $manager): Response {
		$id = $request->query->get('id');

		if (null === $id) {
			$this->addFlash('notice', $this->trans->trans('user.verify.validation', [], 'gatekeeper'));
			return $this->redirectToRoute('user_register');
		}

		$repository = $manager->getRepository(User::class);
		$user = $repository->find($id);

		if (null === $user) {
			$this->addFlash('notice', $this->trans->trans('user.verify.validation', [], 'gatekeeper'));
			return $this->redirectToRoute('user_register');
		}

		// validate email confirmation link, sets User::isVerified=true and persists
		try {
			$this->emailVerifier->handleEmailConfirmation($request, $user);
		} catch (VerifyEmailExceptionInterface $exception) {
			$this->addFlash('error', $this->trans->trans('user.verify.validation', [], 'gatekeeper'));
			#$this->addFlash('verify_email_error', $exception->getReason()); #Default Symfony code.
			return $this->redirectToRoute('user_register');
		}

		$this->addFlash('success', $this->trans->trans('user.verify.success', [], 'security'));
		return $this->redirectToRoute('user_characters');
	}

	#[Route(path: '/characters', name: 'user_characters')]
	public function characters(): Response {
		$user = $this->app->security('user_characters');
		if ($user instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($user->getReason(), [], 'gatekeeper'));
			return new RedirectResponse($user->getRoute());
		}

		return $this->render('user/characters.html.twig', [
			'characters' => $user->getCharacters(),
		]);
	}
}
