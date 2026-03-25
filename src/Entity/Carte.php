<?php

namespace App\Entity;

use App\Repository\CarteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarteRepository::class)]
class Carte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $couleur = null;

    #[ORM\Column(length: 10)]
    private ?string $valeur = null;

    #[ORM\ManyToOne(inversedBy: 'cartes')]
    private ?Joueur $joueur = null;

    #[ORM\ManyToOne(inversedBy: 'cartes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $Partie = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): static
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getValeur(): ?string
    {
        return $this->valeur;
    }

    public function setValeur(string $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getJoueur(): ?Joueur
    {
        return $this->joueur;
    }

    public function setJoueur(?Joueur $joueur): static
    {
        $this->joueur = $joueur;

        return $this;
    }

    public function getPartie(): ?Game
    {
        return $this->Partie;
    }

    public function setPartie(?Game $Partie): static
    {
        $this->Partie = $Partie;

        return $this;
    }
}
