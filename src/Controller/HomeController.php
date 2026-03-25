<?php

namespace App\Controller;

use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[Route('/index', name: 'app_index')]
    public function index(GameRepository $gameRepository): Response
    {
        // On cherche si une partie avec le statut "en_cours" existe déjà en base
        $partieExistante = $gameRepository->findOneBy(['statut' => 'en_cours']);

        // On envoie au template un booléen : true si une partie est en cours, false sinon
        $partieEnCours = $partieExistante !== null;

        return $this->render('home/index.html.twig', [
            'partieEnCours' => $partieEnCours,
        ]);
    }
}
