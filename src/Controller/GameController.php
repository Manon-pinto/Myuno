<?php

namespace App\Controller;

use App\Entity\Carte;
use App\Entity\Joueur;
use App\Repository\CarteRepository;
use App\Repository\GameRepository;
use App\Repository\JoueurRepository;
use App\Service\GameService;
use App\Service\TurnService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class GameController extends AbstractController
{
    private GameService $gameService;
    private TurnService $turnService;
    private GameRepository $gameRepository;
    private JoueurRepository $joueurRepository;
    private CarteRepository $carteRepository;
    private EntityManagerInterface $em;

    public function __construct(
        GameService $gameService,
        TurnService $turnService,
        GameRepository $gameRepository,
        JoueurRepository $joueurRepository,
        CarteRepository $carteRepository,
        EntityManagerInterface $em
    ) {
        $this->gameService      = $gameService;
        $this->turnService      = $turnService;
        $this->gameRepository   = $gameRepository;
        $this->joueurRepository = $joueurRepository;
        $this->carteRepository  = $carteRepository;
        $this->em               = $em;
    }

    #[Route('/start', name: 'app_start')]
    public function start(): Response
    {
            // On termine toutes les parties en cours avant d'en démarrer une nouvelle
        $anciennesParties = $this->gameRepository->findBy(['statut' => 'en_cours']);
        foreach ($anciennesParties as $ancienne) {
            $ancienne->setStatut('termine');
        }

        $this->gameService->initialiserPartie('Joueur 1');

        return $this->redirectToRoute('app_play');
    }

    #[Route('/play', name: 'app_play')]
    public function play(): Response
    {
        $game = $this->gameRepository->findOneBy(['statut' => 'en_cours']);

        if ($game === null) {
               // Si aucune partie n'est en cours, on cherche la dernière partie terminée pour afficher son état final
            $game = $this->gameRepository->findOneBy(['statut' => 'termine'], ['id' => 'DESC']);

            if ($game === null) {
                return $this->redirectToRoute('app_start');
            }
        }

        $joueurHumain = $this->joueurRepository->findOneBy([
            'partie'    => $game,
            'estHumain' => true,
        ]);

        $mainDuJoueur = $this->carteRepository->findBy([
            'joueur' => $joueurHumain,
            'Partie' => $game,
        ]);

        $bots = $this->joueurRepository->findBy([
            'partie'    => $game,
            'estHumain' => false,
        ]);

        // Nombre de cartes par bot
        $nombreCartesParBot = [];
        foreach ($bots as $bot) {
            $nombreCartesParBot[$bot->getNom()] = count($this->carteRepository->findBy([
                'joueur' => $bot,
                'Partie' => $game,
            ]));
        }

        $pile = $game->getPile();

        $estMonTour = $game->getTourActuel() === 0;

        $cartesJouables = [];
        foreach ($mainDuJoueur as $carte) {
            $cartesJouables[$carte->getId()] = $this->turnService->isCardPlayable($carte, $pile, $game->getPileDepioche());
        }

        return $this->render('game/play.html.twig', [
            'game'               => $game,
            'mainDuJoueur'       => $mainDuJoueur,
            'nombreCartesParBot' => $nombreCartesParBot,
            'pile'               => $pile,
            'estMonTour'         => $estMonTour,
            'cartesJouables'     => $cartesJouables,
        ]);
    }

    #[Route('/result', name: 'app_result')]
    public function result(): Response
    {
        $game = $this->gameRepository->findOneBy(['statut' => 'termine'], ['id' => 'DESC']);

        if ($game === null) {
            return $this->redirectToRoute('app_home');
        }

        $gagnant = null;
        foreach ($game->getJoueurs() as $joueur) {
            if (count($this->carteRepository->findBy(['joueur' => $joueur, 'Partie' => $game])) === 0) {
                $gagnant = $joueur;
                break;
            }
        }

        return $this->render('game/result.html.twig', [
            'game'    => $game,
            'gagnant' => $gagnant,
            'joueurs' => $game->getJoueurs(),
        ]);
    }

    #[Route('/draw', name: 'app_draw')]
    public function draw(): Response
    {
        $game = $this->gameRepository->findOneBy(['statut' => 'en_cours']);

        if ($game === null || $game->getTourActuel() !== 0) {
            return $this->redirectToRoute('app_play');
        }

        $joueurHumain = $this->joueurRepository->findOneBy([
            'partie'    => $game,
            'estHumain' => true,
        ]);

        if ($joueurHumain === null) {
            return $this->redirectToRoute('app_play');
        }

        $this->turnService->drawCard($game, $joueurHumain);

        return $this->redirectToRoute('app_play');
    }
}
