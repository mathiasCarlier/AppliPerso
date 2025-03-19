<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $numero_commande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $heure = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?float $prix_total = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $Client = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?Responsable $Responsable = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Statut $Statut = null;

    /**
     * @var Collection<int, LigneCommande>
     */
    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'Commande')]
    private Collection $ligneCommandes;

    public function __construct()
    {
        $this->appartients = new ArrayCollection();
        $this->ligneCommandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroCommande(): ?string
    {
        return $this->numero_commande;
    }

    public function setNumeroCommande(string $numero_commande): static
    {
        $this->numero_commande = $numero_commande;

        return $this;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): static
    {
        $this->heure = $heure;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getPrixTotal(): ?float
    {
        return $this->prix_total;
    }

    public function setPrixTotal(float $prix_total): static
    {
        $this->prix_total = $prix_total;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->Client;
    }

    public function setClient(?Client $Client): static
    {
        $this->Client = $Client;

        return $this;
    }

    public function getResponsable(): ?Responsable
    {
        return $this->Responsable;
    }

    public function setResponsable(?Responsable $Responsable): static
    {
        $this->Responsable = $Responsable;

        return $this;
    }

    public function getStatut(): ?Statut
    {
        return $this->Statut;
    }

    public function setStatut(?Statut $Statut): static
    {
        $this->Statut = $Statut;

        return $this;
    }

    /**
     * @return Collection<int, Appartient>
     */
    public function getAppartients(): Collection
    {
        return $this->appartients;
    }

    public function addAppartient(Appartient $appartient): static
    {
        if (!$this->appartients->contains($appartient)) {
            $this->appartients->add($appartient);
            $appartient->setCommande($this);
        }

        return $this;
    }

    public function removeAppartient(Appartient $appartient): static
    {
        if ($this->appartients->removeElement($appartient)) {
            // set the owning side to null (unless already changed)
            if ($appartient->getCommande() === $this) {
                $appartient->setCommande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LigneCommande>
     */
    public function getLigneCommandes(): Collection
    {
        return $this->ligneCommandes;
    }

    public function addLigneCommande(LigneCommande $ligneCommande): static
    {
        if (!$this->ligneCommandes->contains($ligneCommande)) {
            $this->ligneCommandes->add($ligneCommande);
            $ligneCommande->setCommande($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): static
    {
        if ($this->ligneCommandes->removeElement($ligneCommande)) {
            // set the owning side to null (unless already changed)
            if ($ligneCommande->getCommande() === $this) {
                $ligneCommande->setCommande(null);
            }
        }

        return $this;
    }

    
}
