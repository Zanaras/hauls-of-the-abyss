<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DungeonController extends AbstractController
{
    #[Route('/dungeon', name: 'dungeon_status')]
    public function index(): Response
    {
        return $this->render('dungeon/index.html.twig', [
            'controller_name' => 'DungeonController',
        ]);
    }
}
