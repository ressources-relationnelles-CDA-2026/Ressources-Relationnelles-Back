<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Repository\UtilisateursRepository;
use App\State\UserPasswordProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateursRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_ADMIN') or object == user"),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Post(processor: UserPasswordProcessor::class),
        new Put(
            security: "is_granted('ROLE_ADMIN') or object == user",
            processor: UserPasswordProcessor::class,
            denormalizationContext: ['groups' => ['utilisateurs:profile:write']]
        ),
        new Patch(
            security: "is_granted('ROLE_USER') and object == user",
            denormalizationContext: ['groups' => ['utilisateurs:profile:write']]
        ),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['utilisateurs:read']],
    denormalizationContext: ['groups' => ['utilisateurs:write']]
)]
class Utilisateurs implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['utilisateurs:read', 'resource:read', 'commentaires:read', 'amis:read', 'message:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['utilisateurs:read', 'utilisateurs:write', 'utilisateurs:profile:write', 'resource:read', 'commentaires:read', 'amis:read', 'message:read'])]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['utilisateurs:read', 'utilisateurs:write', 'utilisateurs:profile:write', 'resource:read', 'commentaires:read', 'amis:read', 'message:read'])]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['utilisateurs:read', 'utilisateurs:write', 'utilisateurs:profile:write', 'resource:read'])]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/',
        message: 'Le numéro de téléphone n’est pas valide.'
    )]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    #[Groups(['utilisateurs:read', 'utilisateurs:write', 'utilisateurs:profile:write', 'resource:read'])]
    #[Assert\NotBlank(message: 'L’adresse e-mail est obligatoire.')]
    #[Assert\Email(message: 'L’adresse e-mail n’est pas valide.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L’adresse e-mail ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $motDePasse = null;

    #[ORM\Column(length: 255)]
    #[Groups(['utilisateurs:read', 'utilisateurs:write', 'utilisateurs:profile:write', 'resource:read', 'commentaires:read', 'amis:read', 'message:read'])]
    #[Assert\NotBlank(message: 'Le pseudo est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le pseudo doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le pseudo ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['utilisateurs:read', 'utilisateurs:write', 'resource:read'])]
    private ?string $photoProfil = null;

    #[ORM\Column]
    #[Groups(['utilisateurs:read', 'resource:read'])]
    private ?bool $statusCompte = null;

    #[ORM\Column]
    #[Groups(['utilisateurs:read', 'resource:read'])]
    private ?\DateTime $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['utilisateurs:read'])]
    private ?RolesUtilisateurs $role = null;

    #[ORM\OneToMany(targetEntity: RefreshToken::class, mappedBy: 'utilisateur')]
    #[Groups(['utilisateurs:read'])]
    private Collection $refreshTokens;

    #[ORM\OneToMany(targetEntity: RenitialisationMdp::class, mappedBy: 'utilisateur')]
    #[Groups(['utilisateurs:read'])]
    private Collection $renitialisationMdps;

    #[ORM\OneToMany(targetEntity: Consultations::class, mappedBy: 'utilisateur')]
    #[Groups(['utilisateurs:read'])]
    private Collection $consultations;

    #[ORM\OneToMany(targetEntity: Ressources::class, mappedBy: 'utilisateur')]
    #[Groups(['utilisateurs:read'])]
    private Collection $ressources;

    #[ORM\OneToMany(targetEntity: Commentaires::class, mappedBy: 'utilisateur')]
    #[Groups(['utilisateurs:read'])]
    private Collection $commentaires;

    #[ORM\OneToMany(targetEntity: Partages::class, mappedBy: 'utilisateur')]
    #[Groups(['utilisateurs:read'])]
    private Collection $partages;

    #[ORM\OneToMany(targetEntity: Adorer::class, mappedBy: 'utilisateur')]
    #[Groups(['utilisateurs:read'])]
    private Collection $adorers;

    #[ORM\OneToMany(targetEntity: Favoris::class, mappedBy: 'utilisateur')]
    #[Groups(['utilisateurs:read'])]
    private Collection $favoris;

    #[Groups(['utilisateurs:write', 'utilisateurs:profile:write'])]
    #[SerializedName('password')]
    #[Assert\NotBlank(
        message: 'Le mot de passe est obligatoire.',
        groups: ['Default']
    )]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
    )]
    private ?string $plainPassword = null;

    public function __construct()
    {
        $this->refreshTokens = new ArrayCollection();
        $this->renitialisationMdps = new ArrayCollection();
        $this->consultations = new ArrayCollection();
        $this->ressources = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->partages = new ArrayCollection();
        $this->adorers = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->dateCreation = new \DateTime();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): static
    {
        $this->motDePasse = $motDePasse;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getPhotoProfil(): ?string
    {
        return $this->photoProfil;
    }

    #[Groups(['amis:read', 'message:read'])]
    #[SerializedName('photo_profil')]
    public function getPhotoProfilForAmis(): ?string
    {
        return $this->photoProfil;
    }

    #[Groups(['commentaires:read', 'message:read'])]
    #[SerializedName('photo_profil')]
    public function getPhotoProfilForCommentaires(): ?string
    {
        return $this->photoProfil;
    }

    public function setPhotoProfil(?string $photoProfil): static
    {
        $this->photoProfil = $photoProfil;

        return $this;
    }

    public function isStatusCompte(): ?bool
    {
        return $this->statusCompte;
    }

    public function setStatusCompte(bool $statusCompte): static
    {
        $this->statusCompte = $statusCompte;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getRole(): ?RolesUtilisateurs
    {
        return $this->role;
    }

    public function setRole(?RolesUtilisateurs $role): static
    {
        $this->role = $role;

        return $this;
    }

    #[Groups(['utilisateurs:read'])]
    #[SerializedName('role')]
    public function getRolePayload(): ?array
    {
        if ($this->role === null) {
            return null;
        }

        return [
            'id' => $this->role->getId(),
            'libelle' => $this->role->getLibelle(),
        ];
    }

    /**
     * @return Collection<int, RefreshToken>
     */
    public function getRefreshTokens(): Collection
    {
        return $this->refreshTokens;
    }

    public function addRefreshToken(RefreshToken $refreshToken): static
    {
        if (!$this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens->add($refreshToken);
            $refreshToken->setUtilisateur($this);
        }

        return $this;
    }

    public function removeRefreshToken(RefreshToken $refreshToken): static
    {
        if ($this->refreshTokens->removeElement($refreshToken)) {
            if ($refreshToken->getUtilisateur() === $this) {
                $refreshToken->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RenitialisationMdp>
     */
    public function getRenitialisationMdps(): Collection
    {
        return $this->renitialisationMdps;
    }

    public function addRenitialisationMdp(RenitialisationMdp $renitialisationMdp): static
    {
        if (!$this->renitialisationMdps->contains($renitialisationMdp)) {
            $this->renitialisationMdps->add($renitialisationMdp);
            $renitialisationMdp->setUtilisateur($this);
        }

        return $this;
    }

    public function removeRenitialisationMdp(RenitialisationMdp $renitialisationMdp): static
    {
        if ($this->renitialisationMdps->removeElement($renitialisationMdp)) {
            if ($renitialisationMdp->getUtilisateur() === $this) {
                $renitialisationMdp->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Consultations>
     */
    public function getConsultations(): Collection
    {
        return $this->consultations;
    }

    public function addConsultation(Consultations $consultation): static
    {
        if (!$this->consultations->contains($consultation)) {
            $this->consultations->add($consultation);
            $consultation->setUtilisateur($this);
        }

        return $this;
    }

    public function removeConsultation(Consultations $consultation): static
    {
        if ($this->consultations->removeElement($consultation)) {
            if ($consultation->getUtilisateur() === $this) {
                $consultation->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ressources>
     */
    public function getRessources(): Collection
    {
        return $this->ressources;
    }

    public function addRessource(Ressources $ressource): static
    {
        if (!$this->ressources->contains($ressource)) {
            $this->ressources->add($ressource);
            $ressource->setUtilisateur($this);
        }

        return $this;
    }

    public function removeRessource(Ressources $ressource): static
    {
        if ($this->ressources->removeElement($ressource)) {
            if ($ressource->getUtilisateur() === $this) {
                $ressource->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaires>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaires $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setUtilisateur($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaires $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            if ($commentaire->getUtilisateur() === $this) {
                $commentaire->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Partages>
     */
    public function getPartages(): Collection
    {
        return $this->partages;
    }

    public function addPartage(Partages $partage): static
    {
        if (!$this->partages->contains($partage)) {
            $this->partages->add($partage);
            $partage->setUtilisateur($this);
        }

        return $this;
    }

    public function removePartage(Partages $partage): static
    {
        if ($this->partages->removeElement($partage)) {
            if ($partage->getUtilisateur() === $this) {
                $partage->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Adorer>
     */
    public function getAdorers(): Collection
    {
        return $this->adorers;
    }

    public function addAdorer(Adorer $adorer): static
    {
        if (!$this->adorers->contains($adorer)) {
            $this->adorers->add($adorer);
            $adorer->setUtilisateur($this);
        }

        return $this;
    }

    public function removeAdorer(Adorer $adorer): static
    {
        if ($this->adorers->removeElement($adorer)) {
            if ($adorer->getUtilisateur() === $this) {
                $adorer->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favoris>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavory(Favoris $favory): static
    {
        if (!$this->favoris->contains($favory)) {
            $this->favoris->add($favory);
            $favory->setUtilisateur($this);
        }

        return $this;
    }

    public function removeFavory(Favoris $favory): static
    {
        if ($this->favoris->removeElement($favory)) {
            if ($favory->getUtilisateur() === $this) {
                $favory->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = [];
        if ($this->role?->getLibelle()) {
            $roles[] = $this->role->getLibelle();
        }
        $roles[] = 'ROLE_USER';
        return array_values(array_unique($roles));
    }

    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }
}
