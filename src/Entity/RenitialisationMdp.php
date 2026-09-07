<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\RenitialisationMdpRepository;


#[ORM\Entity(repositoryClass: RenitialisationMdpRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_ADMIN')"),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['renitialisation_mdp:read']],
    denormalizationContext: ['groups' => ['renitialisation_mdp:write']]
)]
class RenitialisationMdp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['renitialisation_mdp:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $tokenReset = null;

    #[ORM\Column]
    #[Groups(['renitialisation_mdp:read', 'renitialisation_mdp:write'])]
    private ?\DateTime $dateDemande = null;

    #[ORM\Column]
    #[Groups(['renitialisation_mdp:read', 'renitialisation_mdp:write'])]
    private ?\DateTime $dateExpiration = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['renitialisation_mdp:read', 'renitialisation_mdp:write'])]
    private ?\DateTime $dateUtilisation = null;

    #[ORM\ManyToOne(inversedBy: 'renitialisationMdps')]
    #[Groups(['renitialisation_mdp:read', 'renitialisation_mdp:write'])]
    private ?Utilisateurs $utilisateur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTokenReset(): ?string
    {
        return $this->tokenReset;
    }

    public function setTokenReset(string $tokenReset): static
    {
        $this->tokenReset = $tokenReset;

        return $this;
    }

    public function getDateDemande(): ?\DateTime
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTime $dateDemande): static
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function getDateExpiration(): ?\DateTime
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(\DateTime $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    public function getDateUtilisation(): ?\DateTime
    {
        return $this->dateUtilisation;
    }

    public function setDateUtilisation(?\DateTime $dateUtilisation): static
    {
        $this->dateUtilisation = $dateUtilisation;

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
}
