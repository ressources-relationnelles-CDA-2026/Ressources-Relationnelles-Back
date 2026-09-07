<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\TagsRessourcesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagsRessourcesRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_ADMIN') or (object.getResource() and object.getResource().getUtilisateur() == user)"),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN') or (object.getResource() and object.getResource().getUtilisateur() == user)"),
        new Delete(security: "is_granted('ROLE_ADMIN') or (object.getResource() and object.getResource().getUtilisateur() == user)"),
    ],
    normalizationContext: ['groups' => ['tags_ressources:read']],
    denormalizationContext: ['groups' => ['tags_ressources:write']]
)]
class TagsRessources
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tags_ressources:read', 'resource:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tagsRessources')]
    #[Groups(['tags_ressources:read', 'tags_ressources:write'])]
    private ?Ressources $resource = null;

    #[ORM\ManyToOne(inversedBy: 'tagsRessources')]
    #[ApiProperty(readableLink: true)]
    #[Groups(['tags_ressources:read', 'tags_ressources:write', 'resource:read'])]
    private ?Tags $tag = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTag(): ?Tags
    {
        return $this->tag;
    }

    public function setTag(?Tags $tag): static
    {
        $this->tag = $tag;

        return $this;
    }
}
