<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EnemyController extends AbstractController
{
    #[Route('/enemy', name: 'app_enemy')]
    public function index(): Response
    {
        return $this->render('enemy/index.html.twig', [
            'controller_name' => 'EnemyController',
        ]);
    }
}
