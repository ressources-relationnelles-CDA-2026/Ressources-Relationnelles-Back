<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\State\CommentaireCreateProcessor;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\AdorerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdorerRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_ADMIN') or object.getUtilisateur() == user"),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Post(
            security: "is_granted('ROLE_USER')",
            processor: CommentaireCreateProcessor::class
        ),
        new Delete(
            security: "is_granted('ADORER_DELETE', object)"
        ),
    ],
    normalizationContext: ['groups' => ['adorer:read']],
    denormalizationContext: ['groups' => ['adorer:write']]
)]
class Adorer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['adorer:read', 'resource:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['adorer:read', 'resource:read'])]
    private ?\DateTime $dateAdorer = null;

    #[ORM\ManyToOne(inversedBy: 'adorers')]
    #[Groups(['adorer:read', 'resource:read'])]
    private ?Utilisateurs $utilisateur = null;

    #[Groups(['adorer:write'])]
    #[SerializedName('id_resource')]
    private ?int $idResource = null;

    #[ORM\ManyToOne(inversedBy: 'adorers')]
    #[Groups(['adorer:read'])]
    private ?Ressources $resource = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAdorer(): ?\DateTime
    {
        return $this->dateAdorer;
    }

    public function setDateAdorer(\DateTime $dateAdorer): static
    {
        $this->dateAdorer = $dateAdorer;

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

    public function getIdResource(): ?int
    {
        return $this->idResource;
    }

    public function setIdResource(?int $idResource): static
    {
        $this->idResource = $idResource;

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
}
