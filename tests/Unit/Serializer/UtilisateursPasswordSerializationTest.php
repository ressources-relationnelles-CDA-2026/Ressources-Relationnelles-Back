<?php

namespace App\Tests\Unit\Serializer;

use App\Entity\Utilisateurs;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Serializer;

final class UtilisateursPasswordSerializationTest extends TestCase
{
    public function testPasswordFieldDenormalizesIntoPlainPassword(): void
    {
        $serializer = new Serializer([
            new ObjectNormalizer(
                new ClassMetadataFactory(new AttributeLoader()),
                new MetadataAwareNameConverter(new ClassMetadataFactory(new AttributeLoader()))
            ),
        ], [
            new JsonEncoder(),
        ]);

        /** @var Utilisateurs $user */
        $user = $serializer->deserialize(
            '{"password":"secret123"}',
            Utilisateurs::class,
            'json',
            ['groups' => ['utilisateurs:write']]
        );

        $this->assertSame('secret123', $user->getPlainPassword());
        $this->assertNull($user->getMotDePasse());
    }

    public function testPasswordFieldIsAcceptedForProfileUpdates(): void
    {
        $serializer = new Serializer([
            new ObjectNormalizer(
                new ClassMetadataFactory(new AttributeLoader()),
                new MetadataAwareNameConverter(new ClassMetadataFactory(new AttributeLoader()))
            ),
        ], [
            new JsonEncoder(),
        ]);

        /** @var Utilisateurs $user */
        $user = $serializer->deserialize(
            '{"password":"new-secret"}',
            Utilisateurs::class,
            'json',
            ['groups' => ['utilisateurs:profile:write']]
        );

        $this->assertSame('new-secret', $user->getPlainPassword());
    }

    public function testPasswordFieldsAreNeverNormalized(): void
    {
        $serializer = new Serializer([
            new ObjectNormalizer(
                new ClassMetadataFactory(new AttributeLoader()),
                new MetadataAwareNameConverter(new ClassMetadataFactory(new AttributeLoader()))
            ),
        ], [
            new JsonEncoder(),
        ]);

        $user = (new Utilisateurs())->setEmail('user@example.com');
        $user->setMotDePasse('$argon2id$hashed-password');

        $json = $serializer->serialize($user, 'json', ['groups' => ['utilisateurs:read']]);

        $this->assertStringNotContainsString('password', $json);
        $this->assertStringNotContainsString('motDePasse', $json);
        $this->assertStringNotContainsString('argon2id', $json);
    }
}
