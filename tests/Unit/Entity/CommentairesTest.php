<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Commentaires;
use PHPUnit\Framework\TestCase;

final class CommentairesTest extends TestCase
{
    public function testDefaultCollectionIsEmpty(): void
    {
        $commentaire = new Commentaires();

        $this->assertCount(0, $commentaire->getCommentaires());
        $this->assertNull($commentaire->getCommentaireParent());
        $this->assertNull($commentaire->getCommentaireParentId());
    }

    public function testAddCommentaireSynchronizesParentRelation(): void
    {
        $parent = new Commentaires();
        $child = new Commentaires();

        $parent->addCommentaire($child);

        $this->assertCount(1, $parent->getCommentaires());
        $this->assertSame($parent, $child->getCommentaireParent());
    }

    public function testRemoveCommentaireClearsParentRelation(): void
    {
        $parent = new Commentaires();
        $child = new Commentaires();

        $parent->addCommentaire($child);
        $parent->removeCommentaire($child);

        $this->assertCount(0, $parent->getCommentaires());
        $this->assertNull($child->getCommentaireParent());
    }

    public function testGetCommentaireParentIdReturnsParentIdentifier(): void
    {
        $parent = new Commentaires();
        $child = new Commentaires();

        $this->setPrivateId($parent, 42);
        $child->setCommentaireParent($parent);

        $this->assertSame(42, $child->getCommentaireParentId());
    }

    private function setPrivateId(Commentaires $commentaire, int $id): void
    {
        $reflectionProperty = new \ReflectionProperty(Commentaires::class, 'id');
        $reflectionProperty->setValue($commentaire, $id);
    }
}
