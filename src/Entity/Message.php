<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\State\CommentaireCreateProcessor;
use App\State\MessagesRecusProvider;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'MESSAGES')]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('MESSAGE_VIEW', object)"),
        new GetCollection(
            uriTemplate: '/messages/recus',
            provider: MessagesRecusProvider::class,
            security: "is_granted('ROLE_USER')"
        ),
        new Post(
            security: "is_granted('ROLE_USER')",
            processor: CommentaireCreateProcessor::class
        ),
        new Put(security: "is_granted('MESSAGE_EDIT', object)"),
        new Delete(security: "is_granted('MESSAGE_DELETE', object)"),
    ],
    normalizationContext: ['groups' => ['message:read']],
    denormalizationContext: ['groups' => ['message:write']]
)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_message')]
    #[Groups(['message:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['message:read', 'message:write'])]
    #[Assert\NotBlank(message: 'Le contenu du message est obligatoire.')]
    private ?string $contenu = null;

    #[ORM\Column(name: 'piece_jointe', nullable: true)]
    #[Groups(['message:read', 'message:write'])]
    private ?string $pieceJointe = null;

    #[ORM\Column(name: 'date_envoie', type: 'datetime')]
    #[Groups(['message:read'])]
    private ?\DateTimeInterface $dateEnvoie = null;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class, inversedBy: 'messagesEnvoyes')]
    #[ORM\JoinColumn(name: 'id_Utilisateurs_1', referencedColumnName: 'id', nullable: false)]
    #[Groups(['message:read'])]
    private ?Utilisateurs $expediteur = null;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class, inversedBy: 'messagesRecus')]
    #[ORM\JoinColumn(name: 'id_Utilisateurs_2', referencedColumnName: 'id', nullable: false)]
    #[Groups(['message:read'])]
    private ?Utilisateurs $destinataire = null;

    #[Groups(['message:write'])]
    #[SerializedName('id_destinataire')]
    private ?int $idDestinataire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getPieceJointe(): ?string
    {
        return $this->pieceJointe;
    }

    public function setPieceJointe(?string $pieceJointe): self
    {
        $this->pieceJointe = $pieceJointe;
        return $this;
    }

    public function getDateEnvoie(): ?\DateTimeInterface
    {
        return $this->dateEnvoie;
    }

    public function setDateEnvoie(\DateTimeInterface $dateEnvoie): self
    {
        $this->dateEnvoie = $dateEnvoie;
        return $this;
    }

    public function getExpediteur(): ?Utilisateurs
    {
        return $this->expediteur;
    }

    public function setExpediteur(?Utilisateurs $expediteur): self
    {
        $this->expediteur = $expediteur;
        return $this;
    }

    public function getDestinataire(): ?Utilisateurs
    {
        return $this->destinataire;
    }

    public function setDestinataire(?Utilisateurs $destinataire): self
    {
        $this->destinataire = $destinataire;
        return $this;
    }

    public function getIdDestinataire(): ?int
    {
        return $this->idDestinataire;
    }

    public function setIdDestinataire(?int $idDestinataire): self
    {
        $this->idDestinataire = $idDestinataire;
        return $this;
    }
}
