<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\State\CommentaireCreateProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use App\Repository\CommentairesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentairesRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('COMMENTAIRE_VIEW', object)"
        ),
        new GetCollection(),
        new Post(
            security: "is_granted('ROLE_USER')",
            processor: CommentaireCreateProcessor::class
        ),
        new Put(
            security: "is_granted('COMMENTAIRE_EDIT', object)"
        ),
        new Delete(
            security: "is_granted('COMMENTAIRE_DELETE', object)"
        ),
    ],
    normalizationContext: ['groups' => ['commentaires:read']],
    denormalizationContext: ['groups' => ['commentaires:write']]
)]
class Commentaires
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['commentaires:read', 'resource:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['commentaires:read', 'commentaires:write', 'resource:read'])]
    #[Assert\NotBlank(message: 'Le commentaire ne peut pas être vide.')]
    #[Assert\Length(
        min: 1,
        max: 5000,
        minMessage: 'Le commentaire doit contenir au moins {{ limit }} caractère.',
        maxMessage: 'Le commentaire ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $contenu = null;

    #[ORM\Column]
    #[Groups(['commentaires:read', 'commentaires:write', 'resource:read'])]
    private ?\DateTime $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ApiProperty(readableLink: true)]
    #[Groups(['commentaires:read', 'resource:read'])]
    #[SerializedName('auteur')]
    private ?Utilisateurs $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[Groups(['commentaires:read', 'commentaires:write'])]
    #[Assert\NotNull(message: 'La ressource associée au commentaire est obligatoire.')]
    private ?Ressources $resource = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'commentaires', cascade: ['remove'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups(['commentaires:read', 'commentaires:write', 'resource:read'])]
    private ?self $commentaireParent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'commentaireParent', orphanRemoval: true)]
    #[Groups(['commentaires:read'])]
    private Collection $commentaires;

    #[Groups(['commentaires:write'])]
    #[SerializedName('id_user')]
    private ?int $idUser = null;

    #[Groups(['commentaires:read', 'resource:read'])]
    #[SerializedName('id_resource')]
    private ?int $idResource = null;

    #[Groups(['commentaires:write'])]
    #[SerializedName('commentaireParentId')]
    private ?int $commentaireParentIdInput = null;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

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

    public function getResource(): ?Ressources
    {
        return $this->resource;
    }

    public function setResource(?Ressources $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    public function getCommentaireParent(): ?self
    {
        return $this->commentaireParent;
    }

    public function setCommentaireParent(?self $commentaireParent): static
    {
        $this->commentaireParent = $commentaireParent;

        return $this;
    }

    #[Groups(['commentaires:read', 'resource:read'])]
    #[SerializedName('commentaireParentId')]
    public function getCommentaireParentId(): ?int
    {
        return $this->commentaireParent?->getId();
    }

    /**
     * @return Collection<int, self>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(self $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setCommentaireParent($this);
        }

        return $this;
    }

    public function removeCommentaire(self $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            if ($commentaire->getCommentaireParent() === $this) {
                $commentaire->setCommentaireParent(null);
            }
        }

        return $this;
    }

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function setIdUser(?int $idUser): static
    {
        $this->idUser = $idUser;

        return $this;
    }

    public function getIdResource(): ?int
    {
        return $this->idResource;
    }

    public function setIdResource(?int $idResource): static
    {
        $this->idResource = $idResource;

        return $this;
    }

    public function getCommentaireParentIdInput(): ?int
    {
        return $this->commentaireParentIdInput;
    }

    public function setCommentaireParentIdInput(?int $commentaireParentIdInput): static
    {
        $this->commentaireParentIdInput = $commentaireParentIdInput;

        return $this;
    }
}
