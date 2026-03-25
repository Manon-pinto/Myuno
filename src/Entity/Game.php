<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    #[ORM\Column]
    private ?int $direction = null;

    #[ORM\Column]
    private ?int $tourActuel = null;

    #[ORM\Column]
    private ?int $pileDepioche = null;

    /**
     * @var Collection<int, Joueur>
     */
    #[ORM\OneToMany(targetEntity: Joueur::class, mappedBy: 'partie')]
    private Collection $joueurs;

    #[ORM\OneToOne(mappedBy: 'Game', cascade: ['persist', 'remove'])]
    private ?Pile $pile = null;

    /**
     * @var Collection<int, Carte>
     */
    #[ORM\OneToMany(targetEntity: Carte::class, mappedBy: 'Partie', orphanRemoval: true)]
    private Collection $cartes;

    public function __construct()
    {
        $this->joueurs = new ArrayCollection();
        $this->cartes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDirection(): ?int
    {
        return $this->direction;
    }

    public function setDirection(int $direction): static
    {
        $this->direction = $direction;

        return $this;
    }

    public function getTourActuel(): ?int
    {
        return $this->tourActuel;
    }

    public function setTourActuel(int $tourActuel): static
    {
        $this->tourActuel = $tourActuel;

        return $this;
    }

    public function getPileDepioche(): ?int
    {
        return $this->pileDepioche;
    }

    public function setPileDepioche(int $pileDepioche): static
    {
        $this->pileDepioche = $pileDepioche;

        return $this;
    }

    /**
     * @return Collection<int, Joueur>
     */
    public function getJoueurs(): Collection
    {
        return $this->joueurs;
    }

    public function addJoueur(Joueur $joueur): static
    {
        if (!$this->joueurs->contains($joueur)) {
            $this->joueurs->add($joueur);
            $joueur->setPartie($this);
        }

        return $this;
    }

    public function removeJoueur(Joueur $joueur): static
    {
        if ($this->joueurs->removeElement($joueur)) {
            // set the owning side to null (unless already changed)
            if ($joueur->getPartie() === $this) {
                $joueur->setPartie(null);
            }
        }

        return $this;
    }

    public function getPile(): ?Pile
    {
        return $this->pile;
    }

    public function setPile(Pile $pile): static
    {
        // set the owning side of the relation if necessary
        if ($pile->getGame() !== $this) {
            $pile->setGame($this);
        }

        $this->pile = $pile;

        return $this;
    }

    /**
     * @return Collection<int, Carte>
     */
    public function getCartes(): Collection
    {
        return $this->cartes;
    }

    public function addCarte(Carte $carte): static
    {
        if (!$this->cartes->contains($carte)) {
            $this->cartes->add($carte);
            $carte->setPartie($this);
        }

        return $this;
    }

    public function removeCarte(Carte $carte): static
    {
        if ($this->cartes->removeElement($carte)) {
            // set the owning side to null (unless already changed)
            if ($carte->getPartie() === $this) {
                $carte->setPartie(null);
            }
        }

        return $this;
    }
}
