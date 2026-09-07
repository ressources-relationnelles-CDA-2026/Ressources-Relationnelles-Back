<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\State\CommentaireCreateProcessor;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ConsultationsRepository;

#[ORM\Entity(repositoryClass: ConsultationsRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_ADMIN') or object.getUtilisateur() == user"),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Post(
            security: "is_granted('ROLE_USER')",
            processor: CommentaireCreateProcessor::class
        ),
            new Put(security: "is_granted('ROLE_ADMIN')"),
            new Delete(security: "is_granted('ROLE_ADMIN') or object.getUtilisateur() == user"),
    ],
    normalizationContext: ['groups' => ['consultations:read']],
    denormalizationContext: ['groups' => ['consultations:write']]
)]
class Consultations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['consultations:read', 'resource:read'])]
    private ?int $id = null;

    #[ORM\Column]
        #[Groups(['consultations:read', 'resource:read'])]
    private ?\DateTime $dateConsultation = null;

    #[ORM\ManyToOne(inversedBy: 'consultations')]
        #[Groups(['consultations:read'])]
    private ?Utilisateurs $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'consultations')]
        #[Groups(['consultations:read'])]
    private ?Ressources $resource = null;

    #[Groups(['consultations:write'])]
    #[SerializedName('id_resource')]
    private ?int $idResource = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateConsultation(): ?\DateTime
    {
        return $this->dateConsultation;
    }

    public function setDateConsultation(\DateTime $dateConsultation): static
    {
        $this->dateConsultation = $dateConsultation;

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

    public function getIdResource(): ?int
    {
        return $this->idResource;
    }

    public function setIdResource(?int $idResource): static
    {
        $this->idResource = $idResource;

        return $this;
    }
}
