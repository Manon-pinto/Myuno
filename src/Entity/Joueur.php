<?php

namespace App\Entity;

use App\Repository\JoueurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JoueurRepository::class)]
class Joueur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?bool $estHumain = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\ManyToOne(inversedBy: 'joueurs')]
    private ?Game $partie = null;

    /**
     * @var Collection<int, Carte>
     */
    #[ORM\OneToMany(targetEntity: Carte::class, mappedBy: 'joueur')]
    private Collection $cartes;

    public function __construct()
    {
        $this->cartes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function isEstHumain(): ?bool
    {
        return $this->estHumain;
    }

    public function setEstHumain(bool $estHumain): static
    {
        $this->estHumain = $estHumain;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getPartie(): ?Game
    {
        return $this->partie;
    }

    public function setPartie(?Game $partie): static
    {
        $this->partie = $partie;

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
            $carte->setJoueur($this);
        }

        return $this;
    }

    public function removeCarte(Carte $carte): static
    {
        if ($this->cartes->removeElement($carte)) {
            // set the owning side to null (unless already changed)
            if ($carte->getJoueur() === $this) {
                $carte->setJoueur(null);
            }
        }

        return $this;
    }
}
