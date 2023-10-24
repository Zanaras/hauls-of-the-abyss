<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WildsController extends AbstractController
{
    #[Route('/wilds', name: 'wilds_status')]
    public function index(): Response
    {
        return $this->render('wilds/index.html.twig', [
            'controller_name' => 'WildsController',
        ]);
    }
}
