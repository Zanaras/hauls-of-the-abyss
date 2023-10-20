<?php

namespace App\Controller;

use App\Service\AppState;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PublicController holds routes that are generally accessible without any authentication (though they can still check for it, and many templates it calls will in order to properly render the nav menu).
 */
class PublicController extends AbstractController {
	#[Route('/', name: 'index')]
	public function index(): Response {
		return $this->render('public/index.html.twig', ['controller_name' => 'PublicController',]);
	}

	#[Route ('/needIP', name: 'ip_needed')]
	public function ipReqAction(AppState $app): Response {
		$app->security('v', 'ip_needed', [], true);
		return $this->render('public/needip.html.twig');
	}
}
