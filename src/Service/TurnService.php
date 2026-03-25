<?php

namespace App\Service;

use App\Entity\Carte;
use App\Entity\Game;
use App\Entity\Joueur;
use App\Entity\Pile;
use App\Repository\CarteRepository;
use Doctrine\ORM\EntityManagerInterface;

class TurnService
{
    private EntityManagerInterface $em;
    private CarteRepository $carteRepository;

    public function __construct(EntityManagerInterface $em, CarteRepository $carteRepository)
    {
        $this->em             = $em;
        $this->carteRepository = $carteRepository;
    }

    // Vérifie si une carte peut être posée sur la pile
    public function isCardPlayable(Carte $carte, Pile $pile, int $pileDepioche = 0): bool
    {
        // Si un +2 est en attente, seul un autre +2 peut contrer
        if ($pileDepioche > 0) {
            return $carte->getValeur() === '+2';
        }

        // Les jokers sont toujours jouables
        if ($carte->getCouleur() === 'noir') {
            return true;
        }

        // Même couleur ou même valeur
        if ($carte->getCouleur() === $pile->getCouleurSommet()) {
            return true;
        }

        if ($carte->getValeur() === $pile->getValeurSommet()) {
            return true;
        }

        return false;
    }

    // Joue une carte : met à jour la pile, applique l'effet, passe au suivant
    public function playCard(Carte $carte, Game $game): void
    {
        $pile = $game->getPile();
        $pile->setCouleurSommet($carte->getCouleur());
        $pile->setValeurSommet($carte->getValeur());

        $carte->setJoueur(null);

        $this->applyEffect($carte, $game);
        $this->passerAuJoueurSuivant($game);

        $this->em->flush();
    }

    // Le joueur pioche une carte (ou plusieurs si un +2 est en attente)
    public function drawCard(Game $game, Joueur $joueur): void
    {
        $nombreDeCartes = $game->getPileDepioche() > 0 ? $game->getPileDepioche() : 1;

        $pioche = $this->carteRepository->findBy([
            'Partie' => $game,
            'joueur' => null,
        ]);

        $cartesDistribuees = 0;
        foreach ($pioche as $carte) {
            if ($cartesDistribuees >= $nombreDeCartes) {
                break;
            }
            $carte->setJoueur($joueur);
            $cartesDistribuees++;
        }

        $game->setPileDepioche(0);
        $this->em->flush();

        $this->passerAuJoueurSuivant($game);
        $this->em->flush();
    }

    // Applique l'effet de la carte (X, S, +2)
    public function applyEffect(Carte $carte, Game $game): void
    {
        $valeur = $carte->getValeur();

        // X : le joueur suivant passe son tour
        if ($valeur === 'X') {
            $this->passerAuJoueurSuivant($game);
        }

        // S : inverse le sens du jeu
        if ($valeur === 'S') {
            $game->setDirection($game->getDirection() === 1 ? -1 : 1);
        }

        // +2 : accumule 2 cartes à piocher pour le suivant
        if ($valeur === '+2') {
            $game->setPileDepioche($game->getPileDepioche() + 2);
        }
    }

    // Passe au joueur suivant selon la direction
    private function passerAuJoueurSuivant(Game $game): void
    {
        $joueurs = $game->getJoueurs()->toArray();

        if (count($joueurs) === 0) {
            return;
        }

        usort($joueurs, function (Joueur $a, Joueur $b) {
            return $a->getPosition() <=> $b->getPosition();
        });

        $nombreDeJoueurs  = count($joueurs);
        $positionActuelle = $game->getTourActuel();
        $indexActuel      = 0;

        foreach ($joueurs as $index => $joueur) {
            if ($joueur->getPosition() === $positionActuelle) {
                $indexActuel = $index;
                break;
            }
        }

        $indexSuivant = $indexActuel + $game->getDirection();

        if ($indexSuivant >= $nombreDeJoueurs) {
            $indexSuivant = 0;
        }

        if ($indexSuivant < 0) {
            $indexSuivant = $nombreDeJoueurs - 1;
        }

        $game->setTourActuel($joueurs[$indexSuivant]->getPosition());
    }
}
