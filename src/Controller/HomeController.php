<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[Route('/home', name: 'app_home_alt')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/start', name: 'app_start')]
    public function start(): Response
    {
        // Initialisation de la partie (placeholder). À compléter avec logique de jeu et persistance.
        return $this->redirectToRoute('app_play');
    }

    #[Route('/play', name: 'app_play')]
    public function play(): Response
    {
        return $this->render('game/play.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
