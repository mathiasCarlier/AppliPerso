<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $Categorie = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?SousCategorie $sous_categorie = null;

    /**
     * @var Collection<int, Avoir>
     */
    #[ORM\OneToMany(targetEntity: Avoir::class, mappedBy: 'Produit')]
    private Collection $avoirs;

    /**
     * @ORM\ManyToMany(targetEntity=Taille::class, mappedBy="produits")
     */
    private $tailles;

    /**
     * @ORM\OneToMany(targetEntity=LigneCommande::class, mappedBy="produit")
     */
    private $ligne_commandes;

    /**
     * @var Collection<int, LigneCommande>
     */
    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'Produit')]
    private Collection $ligneCommandes;

    /**
     * @var Collection<int, Famille>
     */
    #[ORM\OneToMany(targetEntity: Famille::class, mappedBy: 'produit')]
    private Collection $familles;

    #[ORM\Column]
    private ?bool $en_ligne = null;

    #[ORM\Column]
    private ?bool $est_remise = null;

    #[ORM\Column]
    private ?bool $est_menu = null;

    #[ORM\Column]
    private ?int $valeur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ref_produit = null;

    public function __construct()
    {
        $this->appartients = new ArrayCollection();
        $this->avoirs = new ArrayCollection();
        $this->ligneCommandes = new ArrayCollection();
        $this->familles = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->Categorie;
    }

    public function setCategorie(?Categorie $Categorie): static
    {
        $this->Categorie = $Categorie;

        return $this;
    }

    public function getSousCategorie(): ?SousCategorie
    {
        return $this->sous_categorie;
    }

    public function setSousCategorie(?SousCategorie $sous_categorie): static
    {
        $this->sous_categorie = $sous_categorie;

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
            $appartient->setProduit($this);
        }

        return $this;
    }

    public function removeAppartient(Appartient $appartient): static
    {
        if ($this->appartients->removeElement($appartient)) {
            // set the owning side to null (unless already changed)
            if ($appartient->getProduit() === $this) {
                $appartient->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Avoir>
     */
    public function getAvoirs(): Collection
    {
        return $this->avoirs;
    }

    public function addAvoir(Avoir $avoir): static
    {
        if (!$this->avoirs->contains($avoir)) {
            $this->avoirs->add($avoir);
            $avoir->setProduit($this);
        }

        return $this;
    }

    public function removeAvoir(Avoir $avoir): static
    {
        if ($this->avoirs->removeElement($avoir)) {
            // set the owning side to null (unless already changed)
            if ($avoir->getProduit() === $this) {
                $avoir->setProduit(null);
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
            $ligneCommande->setProduit($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): static
    {
        if ($this->ligneCommandes->removeElement($ligneCommande)) {
            // set the owning side to null (unless already changed)
            if ($ligneCommande->getProduit() === $this) {
                $ligneCommande->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Famille>
     */
    public function getFamilles(): Collection
    {
        return $this->familles;
    }

    public function addFamille(Famille $famille): static
    {
        if (!$this->familles->contains($famille)) {
            $this->familles->add($famille);
            $famille->setProduit($this);
        }

        return $this;
    }

    public function removeFamille(Famille $famille): static
    {
        if ($this->familles->removeElement($famille)) {
            // set the owning side to null (unless already changed)
            if ($famille->getProduit() === $this) {
                $famille->setProduit(null);
            }
        }

        return $this;
    }

    public function isEnLigne(): ?bool
    {
        return $this->en_ligne;
    }

    public function setEnLigne(bool $en_ligne): static
    {
        $this->en_ligne = $en_ligne;

        return $this;
    }

    public function isEstRemise(): ?bool
    {
        return $this->est_remise;
    }

    public function setEstRemise(bool $est_remise): static
    {
        $this->est_remise = $est_remise;

        return $this;
    }

    public function isEstMenu(): ?bool
    {
        return $this->est_menu;
    }

    public function setEstMenu(bool $est_menu): static
    {
        $this->est_menu = $est_menu;

        return $this;
    }

    public function getValeur(): ?int
    {
        return $this->valeur;
    }

    public function setValeur(int $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getRefProduit(): ?string
    {
        return $this->ref_produit;
    }

    public function setRefProduit(?string $ref_produit): static
    {
        $this->ref_produit = $ref_produit;

        return $this;
    }
}
