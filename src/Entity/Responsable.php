<?php
namespace App\Entity;

use App\Repository\ResponsableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[UniqueEntity(['login'])]
#[ORM\Entity(repositoryClass: ResponsableRepository::class)]
class Responsable implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $login = null;

    //#[Assert\Length(min: 8)]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $mdp = null;

    #[Assert\Email]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $mail = null;

    #[Assert\Regex(pattern: "/^\d+$/", message: "Le numéro de téléphone doit contenir uniquement des chiffres.")]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'Responsable')]
    private Collection $commandes;

    #[ORM\Column]
    private ?bool $verif_responsable = false;

    #[ORM\ManyToOne(inversedBy: 'responsables')]
    private ?Role $Role = null;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }

    // Méthodes déjà existantes (getters et setters)

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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;
        return $this;
    }

    public function getMdp(): ?string
    {
        return $this->mdp;
    }

    public function setMdp(string $mdp): self
    {
        $this->mdp = $mdp;
        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setResponsable($this);
        }
        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getResponsable() === $this) {
                $commande->setResponsable(null);
            }
        }
        return $this;
    }

    public function isVerifResponsable(): ?bool
    {
        return $this->verif_responsable;
    }

    public function setVerifResponsable(bool $verif_responsable): static
    {
        $this->verif_responsable = $verif_responsable;
        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->Role;
    }

    public function setRole(?Role $Role): static
    {
        $this->Role = $Role;
        return $this;
    }

    // ✅ Implémentation des méthodes de l'interface UserInterface

    public function getRoles(): array
    {
        // Par défaut, l'utilisateur a le rôle ROLE_USER
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Tu peux laisser cette méthode vide si tu n'as pas de données sensibles
    }

    public function getUserIdentifier(): string
    {
        // On retourne le login comme identifiant unique
        return $this->login;
    }

    // ✅ Implémentation de la méthode de PasswordAuthenticatedUserInterface
    public function getPassword(): ?string
    {
        return $this->mdp;
    }
}
