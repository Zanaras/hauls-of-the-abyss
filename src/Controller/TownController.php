<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TownController extends AbstractController
{
    #[Route('/town', name: 'town_status')]
    public function index(): Response
    {
        return $this->render('town/index.html.twig', [
            'controller_name' => 'TownController',
        ]);
    }
}
