<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\AppState;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PublicController holds routes that are generally accessible without any authentication (though they can still check for it, and many templates it calls will in order to properly render the nav menu).
 */
class PublicController extends AbstractController {
	#[Route('/', name: 'public_index')]
	public function index(): Response {
		if ($this->getUser()) {
			$form = null;
		} else {
			$user = new User();
			$form = $this->createForm(RegistrationFormType::class, $user, ['action'=>$this->generateUrl('user_register'), 'labels'=>false]);
		}
		return $this->render('public/index.html.twig', [
			'controller_name' => 'PublicController',
			'form' => $form->createView(),
		]);
	}

	#[Route ('/needIP', name: 'public_ip_needed')]
	public function needIp(AppState $app): Response {
		$app->security('v', 'ip_needed', [], true);
		return $this->render('public/needip.html.twig');
	}

	#[Route ('/terms', name: 'public_terms')]
	public function terms(AppState $app): Response {
		return $this->render('public/terms.html.twig');
	}
}
