<?php

namespace App\Entity;

use App\Repository\PileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PileRepository::class)]
class Pile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $couleurSommet = null;

    #[ORM\Column(length: 10)]
    private ?string $valeurSommet = null;

    #[ORM\OneToOne(inversedBy: 'pile', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $Game = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCouleurSommet(): ?string
    {
        return $this->couleurSommet;
    }

    public function setCouleurSommet(string $couleurSommet): static
    {
        $this->couleurSommet = $couleurSommet;

        return $this;
    }

    public function getValeurSommet(): ?string
    {
        return $this->valeurSommet;
    }

    public function setValeurSommet(string $valeurSommet): static
    {
        $this->valeurSommet = $valeurSommet;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->Game;
    }

    public function setGame(Game $Game): static
    {
        $this->Game = $Game;

        return $this;
    }
}
