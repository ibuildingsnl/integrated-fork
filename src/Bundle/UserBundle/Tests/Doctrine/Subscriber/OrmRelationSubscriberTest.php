<?php

declare(strict_types=1);

namespace Integrated\Bundle\UserBundle\Tests\Doctrine\Subscriber;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\UserBundle\Doctrine\Subscriber\OrmRelationSubscriber;
use Integrated\Bundle\UserBundle\Model\User;
use PHPUnit\Framework\TestCase;

final class OrmRelationSubscriberTest extends TestCase
{
    public function testPostLoadHydratesRelationInstanceWithoutUsingAccessibleReflection(): void
    {
        $user = new User();
        $this->setProtectedProperty($user, 'relation', 'content-123');

        $relationInstance = new \stdClass();

        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects(self::once())
            ->method('find')
            ->with('content-123')
            ->willReturn($relationInstance);

        $documentManager = $this->createMock(ObjectManager::class);
        $documentManager
            ->expects(self::once())
            ->method('getRepository')
            ->with('Integrated\\Bundle\\ContentBundle\\Document\\Content\\Content')
            ->willReturn($repository);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->expects(self::once())
            ->method('getManager')
            ->willReturn($documentManager);

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects(self::exactly(2))
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(User::class));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager
            ->expects(self::once())
            ->method('getClassMetadata')
            ->with(User::class)
            ->willReturn($classMetadata);

        $subscriber = new OrmRelationSubscriber($registry);
        $subscriber->postLoad(new LifecycleEventArgs($user, $objectManager));

        self::assertSame($relationInstance, $this->getProtectedProperty($user, 'relation_instance'));
    }

    private function setProtectedProperty(object $object, string $name, mixed $value): void
    {
        $property = new \ReflectionProperty($object, $name);
        $property->setValue($object, $value);
    }

    private function getProtectedProperty(object $object, string $name): mixed
    {
        $property = new \ReflectionProperty($object, $name);

        return $property->getValue($object);
    }
}
