<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\TagsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagsRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['tags:read']],
    denormalizationContext: ['groups' => ['tags:write']]
)]
class Tags
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tags:read', 'resource:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tags:read', 'tags:write', 'resource:read'])]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tags:read', 'tags:write', 'resource:read'])]
    private ?string $couleur = null;

    #[ORM\OneToMany(targetEntity: TagsRessources::class, mappedBy: 'tag')]
    #[Groups(['tags:read'])]
    private Collection $tagsRessources;

    public function __construct()
    {
        $this->tagsRessources = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): static
    {
        $this->couleur = $couleur;

        return $this;
    }

    /**
     * @return Collection<int, TagsRessources>
     */
    public function getTagsRessources(): Collection
    {
        return $this->tagsRessources;
    }

    public function addTagsRessource(TagsRessources $tagsRessource): static
    {
        if (!$this->tagsRessources->contains($tagsRessource)) {
            $this->tagsRessources->add($tagsRessource);
            $tagsRessource->setTag($this);
        }

        return $this;
    }

    public function removeTagsRessource(TagsRessources $tagsRessource): static
    {
        if ($this->tagsRessources->removeElement($tagsRessource)) {
            if ($tagsRessource->getTag() === $this) {
                $tagsRessource->setTag(null);
            }
        }

        return $this;
    }
}
