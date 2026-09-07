<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\PartagesRepository;
use App\State\PartageCreateProcessor;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartagesRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('PARTAGE_VIEW', object)"
        ),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Post(
            security: "is_granted('ROLE_USER')",
            processor: PartageCreateProcessor::class
        ),
        new Delete(
            security: "is_granted('PARTAGE_DELETE', object)"
        ),
    ],
    normalizationContext: ['groups' => ['partages:read']],
    denormalizationContext: ['groups' => ['partages:write']]
)]
class Partages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['partages:read', 'resource:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['partages:read', 'resource:read'])]
    private ?\DateTime $datePartage = null;

    #[ORM\ManyToOne(inversedBy: 'partages')]
    #[Groups(['partages:read', 'resource:read'])]
    private ?Utilisateurs $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'partages')]
    #[Groups(['partages:read', 'partages:write', 'resource:read'])]
    private ?Utilisateurs $utilisateur2 = null;

    #[ORM\ManyToOne(inversedBy: 'partages')]
    #[Groups(['partages:read', 'partages:write'])]
    private ?Ressources $resource = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePartage(): ?\DateTime
    {
        return $this->datePartage;
    }

    public function setDatePartage(\DateTime $datePartage): static
    {
        $this->datePartage = $datePartage;

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

    public function getUtilisateur2(): ?Utilisateurs
    {
        return $this->utilisateur2;
    }

    public function setUtilisateur2(?Utilisateurs $utilisateur2): static
    {
        $this->utilisateur2 = $utilisateur2;

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
