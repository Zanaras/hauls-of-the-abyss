<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\AppState;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PublicController holds routes that are generally accessible without any authentication (though they can still check for it, and many templates it calls will in order to properly render the nav menu).
 */
class PublicController extends AbstractController {
	#[Route('/', name: 'public_index')]
	public function index(EntityManagerInterface $em): Response {
		$query = $em->createQuery('SELECT j, c from App:Journal j JOIN j.character c WHERE j.public = true AND j.graphic = false AND j.pending_review = false AND j.GM_private = false AND j.GM_graphic = false ORDER BY j.date DESC')->setMaxResults(3);
		$journals = $query->getResult();
		if ($this->getUser()) {
			$form = null;
		} else {
			$user = new User();
			$form = $this->createForm(RegistrationFormType::class, $user, ['action'=>$this->generateUrl('user_register'), 'labels'=>false]);
		}
		$update = $em->createQuery('SELECT u from App:UpdateNote u ORDER BY u.id DESC')->setMaxResults(1)->getResult();
		return $this->render('public/index.html.twig', [
			'controller_name' => 'PublicController',
			'form' => $form?->createView(),
			'journals' => $journals,
			'update' => $update,
		]);
	}

	#[Route ('/needIP', name: 'public_ip_needed')]
	public function needIp(AppState $app): Response {
		$app->security('ip_needed', [], true);
		return $this->render('public/needip.html.twig');
	}

	#[Route ('/terms', name: 'public_terms')]
	public function terms(AppState $app): Response {
		return $this->render('public/terms.html.twig');
	}
}
