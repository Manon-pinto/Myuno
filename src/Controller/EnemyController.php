<?php

namespace App\Controller;

use App\Repository\JoueurRepository;
use App\Service\GameService;
use App\Service\TurnService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EnemyController extends AbstractController
{
    #[Route('/enemy', name: 'app_enemy', methods: ['GET'])]
    public function index(
        Request $request,
        JoueurRepository $joueurRepository,
        TurnService $turnService,
        GameService $gameService,
        EntityManagerInterface $em
    ): Response {
        $id = $request->query->get('id');

        if (!$id) {
            return $this->redirectToRoute('app_play');
        }

        $joueur = $joueurRepository->find($id);

        // Sécurité : doit être un bot existant
        if (!$joueur || $joueur->isEstHumain() === true) {
            return $this->redirectToRoute('app_play');
        }

        $game = $joueur->getPartie();
        $pile = $game ? $game->getPile() : null;

        if (!$game || !$pile) {
            return $this->redirectToRoute('app_play');
        }

        // Sécurité : c'est bien le tour de ce bot
        if ($game->getTourActuel() !== $joueur->getPosition()) {
            return $this->redirectToRoute('app_play');
        }

        // On cherche une carte jouable dans la main du bot
        $carteAJouer = null;
        foreach ($joueur->getCartes() as $carte) {
            if ($turnService->isCardPlayable($carte, $pile, $game->getPileDepioche())) {
                $carteAJouer = $carte;
                break;
            }
        }

        if ($carteAJouer !== null) {
            $turnService->playCard($carteAJouer, $game);
            $gameService->verifierVictoire($joueur, $game);
        } else {
            // Aucune carte jouable : le bot pioche
            $turnService->drawCard($game, $joueur);
        }

        return $this->redirectToRoute('app_play');
    }
}
