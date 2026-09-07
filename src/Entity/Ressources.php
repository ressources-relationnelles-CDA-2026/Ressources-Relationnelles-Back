<?php

namespace App\Entity;

use App\Repository\RessourcesRepository;
use App\State\RessourcesCollectionProvider;
use App\State\RessourceWriteProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

enum VisibiliteStatut: string
{
    case PRIVE  = 'private';
    case PUBLIC = 'public';
    case AMI    = 'friend';

    public function label(): string
    {
        return match ($this) {
            self::PRIVE  => 'PrivÃ©',
            self::PUBLIC => 'Public',
            self::AMI    => 'Amis',
        };
    }
}

#[ORM\Entity(repositoryClass: RessourcesRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('RESSOURCE_VIEW', object)"
        ),

        new GetCollection(
            provider: RessourcesCollectionProvider::class
        ),

        new Post(
            security: "is_granted('ROLE_USER')",
            processor: RessourceWriteProcessor::class
        ),

        new Patch(
            uriTemplate: '/ressources/{id}/validation',
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['resource:admin_write']]
        ),

        new Put(
            security: "is_granted('RESSOURCE_EDIT', object)",
            processor: RessourceWriteProcessor::class
        ),

        new Delete(
            security: "is_granted('RESSOURCE_DELETE', object)"
        ),
    ],

    normalizationContext: ['groups' => ['resource:read']],
    denormalizationContext: ['groups' => ['resource:write']]
)]
class Ressources
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['resource:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['resource:read', 'resource:write'])]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['resource:read', 'resource:write'])]
    #[Assert\NotBlank(message: 'Le contenu est obligatoire.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Le contenu doit contenir au moins {{ limit }} caractères.'
    )]
    private ?string $contenu = null;

    #[ORM\Column]
    #[Groups(['resource:read',  'resource:write', 'resource:admin_write'])]
    private ?bool $valide = false;

    #[ORM\Column]
    #[Groups(['resource:read'])]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['resource:read'])]
    private ?\DateTime $dateModification = null;

    #[ORM\Column]
    #[Groups(['resource:read', 'resource:write', 'resource:admin_write'])]
    #[Assert\NotNull(message: 'La visibilité est obligatoire.')]
    private ?bool $estVisible = true;

    #[ORM\Column(type: 'string', enumType: VisibiliteStatut::class, length: 10)]
    #[Groups(['resource:read', 'resource:write'])]
    private VisibiliteStatut $visibilite = VisibiliteStatut::PUBLIC;

    #[ORM\ManyToOne(inversedBy: 'ressources')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(readableLink: true)]
    #[Groups(['resource:read'])]
    private ?Utilisateurs $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'ressources')]
    #[ORM\JoinColumn(
        name: 'categorie_id',
        referencedColumnName: 'id',
        nullable: false
    )]
    #[ApiProperty(readableLink: true)]
    #[Groups(['resource:read', 'resource:write'])]
    #[Assert\NotNull(message: 'La catégorie est obligatoire.')]
    private ?Categories $categorie = null;

    #[ORM\OneToMany(
        targetEntity: Medias::class,
        mappedBy: 'resource',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    #[ApiProperty(readableLink: true)]
    #[Groups(['resource:read'])]
    private Collection $medias;

    #[ORM\OneToMany(
        targetEntity: TagsRessources::class,
        mappedBy: 'resource',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    #[ApiProperty(readableLink: true)]
    #[Groups(['resource:read'])]
    private Collection $tagsRessources;

    #[ORM\OneToMany(
        targetEntity: Consultations::class,
        mappedBy: 'resource',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    #[ApiProperty(readableLink: true)]
    #[Groups(['resource:read'])]
    private Collection $consultations;

    #[ORM\OneToMany(
        targetEntity: Commentaires::class,
        mappedBy: 'resource',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    #[ApiProperty(readableLink: true)]
    #[Groups(['resource:read'])]
    private Collection $commentaires;

    #[ORM\OneToMany(
        targetEntity: Partages::class,
        mappedBy: 'resource',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    #[ApiProperty(readableLink: true)]
    #[Groups(['resource:read'])]
    private Collection $partages;

    #[ORM\OneToMany(
        targetEntity: Adorer::class,
        mappedBy: 'resource',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    #[ApiProperty(readableLink: true)]
    #[Groups(['resource:read'])]
    private Collection $adorers;

    #[ORM\OneToMany(
        targetEntity: Favoris::class,
        mappedBy: 'resource',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    #[ApiProperty(readableLink: true)]
    #[Groups(['resource:read'])]
    private Collection $favoris;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();

        $this->medias = new ArrayCollection();
        $this->tagsRessources = new ArrayCollection();
        $this->consultations = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->partages = new ArrayCollection();
        $this->adorers = new ArrayCollection();
        $this->favoris = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function isValide(): ?bool
    {
        return $this->valide;
    }

    public function setValide(bool $valide): static
    {
        $this->valide = $valide;

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

    public function getDateModification(): ?\DateTime
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTime $dateModification): static
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    public function isEstVisible(): ?bool
    {
        return $this->estVisible;
    }

    public function setEstVisible(bool $estVisible): static
    {
        $this->estVisible = $estVisible;

        return $this;
    }

    public function getVisibilite(): VisibiliteStatut
    {
        return $this->visibilite;
    }

    public function setVisibilite(VisibiliteStatut $visibilite): static
    {
        $this->visibilite = $visibilite;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateurs
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateurs $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getCategorie(): ?Categories
    {
        return $this->categorie;
    }

    public function setCategorie(?Categories $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    #[Groups(['resource:read'])]
    #[SerializedName('categorie_id')]
    public function getCategorieId(): ?int
    {
        return $this->categorie?->getId();
    }

    /**
     * @return Collection<int, Commentaires>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    /**
     * @return Collection<int, Favoris>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    /**
     * @return Collection<int, Adorer>
     */
    public function getAdorers(): Collection
    {
        return $this->adorers;
    }

    /**
     * @return Collection<int, Partages>
     */
    public function getPartages(): Collection
    {
        return $this->partages;
    }

    /**
     * @return Collection<int, Consultations>
     */
    public function getConsultations(): Collection
    {
        return $this->consultations;
    }

    /**
     * @return Collection<int, Medias>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    /**
     * @return Collection<int, TagsRessources>
     */
    public function getTagsRessources(): Collection
    {
        return $this->tagsRessources;
    }

    #[Groups(['resource:read'])]
    public function getTags(): array
    {
        $tags = [];

        foreach ($this->tagsRessources as $tr) {
            $tag = $tr->getTag();

            if ($tag !== null) {
                $tags[] = $tag;
            }
        }

        return $tags;
    }
}
