<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoriesRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['categories:read']],
    denormalizationContext: ['groups' => ['categories:write']]
)]
class Categories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['categories:read', 'resource:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['categories:read', 'categories:write', 'resource:read'])]
    #[Assert\NotBlank(message: 'Le libellé de la catégorie est obligatoire.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le libellé de la catégorie ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    #[Groups(['categories:read', 'categories:write', 'resource:read'])]
    #[Assert\NotBlank(message: 'La couleur de la catégorie est obligatoire.')]
    #[Assert\CssColor(message: 'La couleur indiquée n’est pas valide.')]
    private ?string $couleur = null;

    #[ORM\OneToMany(targetEntity: Ressources::class, mappedBy: 'categorie')]
    #[Groups(['categories:read'])]
    private Collection $ressources;

    public function __construct()
    {
        $this->ressources = new ArrayCollection();
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
            $ressource->setCategorie($this);
        }

        return $this;
    }

    public function removeRessource(Ressources $ressource): static
    {
        if ($this->ressources->removeElement($ressource)) {
            if ($ressource->getCategorie() === $this) {
                $ressource->setCategorie(null);
            }
        }

        return $this;
    }
}
