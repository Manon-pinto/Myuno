<?php

namespace App\Service;

use App\Entity\Game;
use App\Entity\Joueur;
use App\Entity\Carte;
use App\Entity\Pile;
use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function initialiserPartie(string $nomJoueur): Game
    {
        $game = new Game();
        $game->setStatut('en_cours');
        $game->setDirection(1);  // 1 = sens normal
        $game->setTourActuel(0); // position 0 commence
        $game->setPileDepioche(0);
        $this->em->persist($game);

        // Joueur humain
        $humain = new Joueur();
        $humain->setNom($nomJoueur);
        $humain->setEstHumain(true);
        $humain->setPosition(0);
        $humain->setPartie($game);
        $this->em->persist($humain);

        // Les 3 bots
        $bot1 = new Joueur();
        $bot1->setNom('Pierre');
        $bot1->setEstHumain(false);
        $bot1->setPosition(1);
        $bot1->setPartie($game);
        $this->em->persist($bot1);

        $bot2 = new Joueur();
        $bot2->setNom('Bob');
        $bot2->setEstHumain(false);
        $bot2->setPosition(2);
        $bot2->setPartie($game);
        $this->em->persist($bot2);

        $bot3 = new Joueur();
        $bot3->setNom('Jacques');
        $bot3->setEstHumain(false);
        $bot3->setPosition(3);
        $bot3->setPartie($game);
        $this->em->persist($bot3);

        $joueurs = [$humain, $bot1, $bot2, $bot3];

        $deck = $this->creerDeck($game);
        shuffle($deck);

        // 7 cartes par joueur
        foreach ($joueurs as $joueur) {
            for ($i = 0; $i < 7; $i++) {
                $carte = array_shift($deck);
                $carte->setJoueur($joueur);
            }
        }

        // Première carte de la pile
        $premiereCarte = array_shift($deck);

        $pile = new Pile();
        $pile->setCouleurSommet($premiereCarte->getCouleur());
        $pile->setValeurSommet($premiereCarte->getValeur());
        $pile->setGame($game);
        $this->em->persist($pile);

        $this->em->flush();

        return $game;
    }

    private function creerDeck(Game $game): array
    {
        $deck = [];
        $couleurs = ['rouge', 'jaune', 'vert', 'bleu'];

        foreach ($couleurs as $couleur) {
            $deck[] = $this->creerCarte($couleur, '0', $game);

            foreach (['1','2','3','4','5','6','7','8','9'] as $chiffre) {
                $deck[] = $this->creerCarte($couleur, $chiffre, $game);
                $deck[] = $this->creerCarte($couleur, $chiffre, $game);
            }

            // Cartes spéciales x2
            foreach (['+2', 'S', 'X'] as $special) {
                $deck[] = $this->creerCarte($couleur, $special, $game);
                $deck[] = $this->creerCarte($couleur, $special, $game);
            }
        }

        return $deck;
    }

    private function creerCarte(string $couleur, string $valeur, Game $game): Carte
    {
        $carte = new Carte();
        $carte->setCouleur($couleur);
        $carte->setValeur($valeur);
        $carte->setPartie($game);
        $carte->setJoueur(null);
        $this->em->persist($carte);

        return $carte;
    }

    public function carteJouable(Carte $carte, Pile $pile): bool
    {
        if ($carte->getCouleur() === 'noir') return true;
        if ($carte->getCouleur() === $pile->getCouleurSommet()) return true;
        if ($carte->getValeur() === $pile->getValeurSommet()) return true;

        return false;
    }

    public function jouerCarte(Carte $carte, Pile $pile, Game $game, ?string $couleurChoisie = null): void
    {
        $pile->setCouleurSommet($carte->getCouleur() === 'noir' ? $couleurChoisie : $carte->getCouleur());
        $pile->setValeurSommet($carte->getValeur());
        $carte->setJoueur(null);

        if ($carte->getValeur() === 'S') {
            $game->setDirection($game->getDirection() === 1 ? -1 : 1);
        }

        if ($carte->getValeur() === 'X') {
            $this->joueurSuivant($game);
        }

        if ($carte->getValeur() === '+2') {
            $joueurPenalise = $this->joueurSuivant($game);
            $this->piocher($joueurPenalise, $game, 2);
            $this->joueurSuivant($game);
        }

        $this->em->flush();
    }

    public function piocher(Joueur $joueur, Game $game, int $nb = 1): void
    {
        $pioche = $this->em->getRepository(Carte::class)->findBy([
            'Partie' => $game,
            'joueur' => null,
        ]);

        $cartesDonnees = 0;
        foreach ($pioche as $carte) {
            if ($cartesDonnees >= $nb) break;
            $carte->setJoueur($joueur);
            $cartesDonnees++;
        }

        $this->em->flush();
    }

    public function joueurSuivant(Game $game): Joueur
    {
        $joueurs = $this->em->getRepository(Joueur::class)->findBy(
            ['partie' => $game],
            ['position' => 'ASC']
        );

        $n            = count($joueurs);
        $prochainTour = ($game->getTourActuel() + $game->getDirection() + $n) % $n;

        $game->setTourActuel($prochainTour);
        $this->em->flush();

        return $joueurs[$prochainTour];
    }

    public function jouerTourBot(Joueur $bot, Game $game, Pile $pile): void
    {
        $main = $this->em->getRepository(Carte::class)->findBy([
            'joueur' => $bot,
            'Partie' => $game,
        ]);

        foreach ($main as $carte) {
            if ($this->carteJouable($carte, $pile)) {
                $couleur = $carte->getCouleur() === 'noir' ? 'rouge' : null;
                $this->jouerCarte($carte, $pile, $game, $couleur);
                return;
            }
        }

        // Aucune carte jouable : pioche
        $this->piocher($bot, $game, 1);
    }

    public function verifierVictoire(Joueur $joueur, Game $game): bool
    {
        $nombreDeCartes = $this->em->getRepository(Carte::class)->count([
            'joueur' => $joueur,
            'Partie' => $game,
        ]);

        if ($nombreDeCartes === 0) {
            $game->setStatut('termine');
            $this->em->flush();
            return true;
        }

        return false;
    }
}
