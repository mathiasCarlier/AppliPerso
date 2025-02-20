<?php

namespace App\Entity;

use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneCommandeRepository::class)]
class LigneCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $numero_ordre = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\ManyToOne(inversedBy: 'ligneCommandes')]
    private ?Produit $Produit = null;

    #[ORM\ManyToOne(inversedBy: 'ligneCommandes')]
    private ?Taille $Taille = null;

    #[ORM\ManyToOne(inversedBy: 'ligneCommandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commande $Commande = null;

    #[ORM\Column(nullable: true)]
    private ?int $numero_menu = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroOrdre(): ?int
    {
        return $this->numero_ordre;
    }

    public function setNumeroOrdre(?int $numero_ordre): static
    {
        $this->numero_ordre = $numero_ordre;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->Produit;
    }

    public function setProduit(?Produit $Produit): static
    {
        $this->Produit = $Produit;

        return $this;
    }

    public function getTaille(): ?Taille
    {
        return $this->Taille;
    }

    public function setTaille(?Taille $Taille): static
    {
        $this->Taille = $Taille;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->Commande;
    }

    public function setCommande(?Commande $Commande): static
    {
        $this->Commande = $Commande;

        return $this;
    }

    public function getNumeroMenu(): ?int
    {
        return $this->numero_menu;
    }

    public function setNumeroMenu(?int $numero_menu): static
    {
        $this->numero_menu = $numero_menu;

        return $this;
    }
}
