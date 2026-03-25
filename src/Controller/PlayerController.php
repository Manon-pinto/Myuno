<?php

namespace App\Controller;

use App\Repository\CarteRepository;
use App\Repository\GameRepository;
use App\Service\GameService;
use App\Service\TurnService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PlayerController extends AbstractController
{
    private TurnService $turnService;
    private CarteRepository $carteRepository;
    private GameRepository $gameRepository;
    private GameService $gameService;

    public function __construct(
        TurnService $turnService,
        CarteRepository $carteRepository,
        GameRepository $gameRepository,
        GameService $gameService
    ) {
        $this->turnService     = $turnService;
        $this->carteRepository = $carteRepository;
        $this->gameRepository  = $gameRepository;
        $this->gameService     = $gameService;
    }

    #[Route('/player', name: 'app_player')]
    public function jouerCarte(Request $request): Response
    {
        $idCarte = $request->query->get('id');

        if ($idCarte === null) {
            return $this->redirectToRoute('app_play');
        }

        $carte = $this->carteRepository->find($idCarte);

        if ($carte === null) {
            return $this->redirectToRoute('app_play');
        }

        $game = $this->gameRepository->findOneBy(['statut' => 'en_cours']);

        if ($game === null) {
            return $this->redirectToRoute('app_play');
        }

        $joueurDeLaCarte = $carte->getJoueur();

        // Sécurité : la carte doit appartenir au joueur humain
        if ($joueurDeLaCarte === null || $joueurDeLaCarte->isEstHumain() === false) {
            return $this->redirectToRoute('app_play');
        }

        // Sécurité : c'est bien son tour
        if ($game->getTourActuel() !== 0) {
            return $this->redirectToRoute('app_play');
        }

        $pile = $game->getPile();
        $carteEstJouable = $this->turnService->isCardPlayable($carte, $pile, $game->getPileDepioche());

        if ($carteEstJouable === false) {
            return $this->redirectToRoute('app_play');
        }

        $this->turnService->playCard($carte, $game);
        $this->gameService->verifierVictoire($joueurDeLaCarte, $game);

        return $this->redirectToRoute('app_play');
    }
}
