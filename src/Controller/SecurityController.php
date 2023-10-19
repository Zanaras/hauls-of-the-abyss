<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

/**
 * Security Controller is for anything that creates, modifies, or removes user accounts or user security controls, or lets someone login or logout.
 */
class SecurityController extends AbstractController {

	private EmailVerifier $emailVerifier;
	private TranslatorInterface $trans;

	public function __construct(EmailVerifier $emailVerifier, TranslatorInterface $trans)
	{
		$this->emailVerifier = $emailVerifier;
		$this->trans = $trans;
	}

	#[Route(path: '/login', name: 'user_login')]
	public function login(AuthenticationUtils $authenticationUtils): Response
	{
		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();

		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();

		return $this->render('login/login.html.twig', [
			'last_username' => $lastUsername,
			'error' => $error,
		]);
	}

	#[Route(path: '/logout', name: 'user_logout')]
	public function logout(): void
	{
		throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
	}

	#[Route('/register', name: 'user_register')]
	public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response {
		$user = new User();
		$form = $this->createForm(RegistrationFormType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			// encode the plain password
			$user->setPassword(
				$userPasswordHasher->hashPassword(
					$user,
					$form->get('plainPassword')->getData()
				)
			);

			$entityManager->persist($user);
			$entityManager->flush();

			// generate a signed url and email it to the user
			#TODO: Run this through the tranlsator system.
			$this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
				(new TemplatedEmail())
					->from(new Address('hota-server@lemuriacommunity.org', 'Hauls of the Abyss Game'))
					->to($user->getEmail())
					->subject('Please Confirm your Email')
					->htmlTemplate('registration/confirmation_email.html.twig')
			);
			// do anything else you need here, like send an email

			$this->addFlash('notice', $this->trans->trans('user.register.emailSent', [], 'security'));
			return $this->redirectToRoute('index');
		}

		return $this->render('registration/register.html.twig', [
			'registrationForm' => $form->createView(),
		]);
	}

	#[Route('/verify/email', name: 'user_verify_email')]
	public function verifyUserEmail(Request $request, EntityManagerInterface $manager): Response {
		$id = $request->query->get('id');

		if (null === $id) {
			$this->addFlash('notice', $this->trans->trans('user.verify.validation', [], 'gatekeeper'));
			return $this->redirectToRoute('app_register');
		}

		$repository = $manager->getRepository(User::class);
		$user = $repository->find($id);

		if (null === $user) {
			$this->addFlash('notice', $this->trans->trans('user.verify.validation', [], 'gatekeeper'));
			return $this->redirectToRoute('app_register');
		}

		// validate email confirmation link, sets User::isVerified=true and persists
		try {
			$this->emailVerifier->handleEmailConfirmation($request, $user);
		} catch (VerifyEmailExceptionInterface $exception) {
			$this->addFlash('error', $this->trans->trans('user.verify.validation', [], 'gatekeeper'));
			#$this->addFlash('verify_email_error', $exception->getReason()); #Default Symfony code.
			return $this->redirectToRoute('app_register');
		}

		$this->addFlash('success', $this->trans->trans('user.verify.success', [], 'security'));
		return $this->redirectToRoute('account_characters');
	}
}
