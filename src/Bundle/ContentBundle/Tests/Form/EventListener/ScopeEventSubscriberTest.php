<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Form\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Form\EventListener\ScopeEventSubscriber;
use Integrated\Bundle\UserBundle\Model\Scope;
use PHPUnit\Framework\TestCase;

final class ScopeEventSubscriberTest extends TestCase
{
    public function testPostLoadHydratesScopeInstanceWithoutUsingAccessibleReflection(): void
    {
        $channel = new Channel();
        $this->setProtectedProperty($channel, 'scope', 'scope-123');

        $scopeReference = new Scope('scope-123');

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager
            ->expects(self::once())
            ->method('getReference')
            ->with(Scope::class, 'scope-123')
            ->willReturn($scopeReference);

        $classMetadata = new ClassMetadata(Channel::class);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects(self::exactly(2))
            ->method('getClassMetadata')
            ->with(Channel::class)
            ->willReturn($classMetadata);

        $eventArgs = $this->createMock(LifecycleEventArgs::class);
        $eventArgs
            ->expects(self::once())
            ->method('getDocument')
            ->willReturn($channel);
        $eventArgs
            ->expects(self::once())
            ->method('getDocumentManager')
            ->willReturn($documentManager);

        $subscriber = new ScopeEventSubscriber($entityManager);

        $this->withDeprecationsConvertedToExceptions(
            static fn () => $subscriber->postLoad($eventArgs)
        );

        self::assertSame($scopeReference, $this->getProtectedProperty($channel, 'scopeInstance'));
    }

    private function withDeprecationsConvertedToExceptions(callable $callback): void
    {
        set_error_handler(
            static function (int $severity, string $message, string $file, int $line): bool {
                if ($severity === \E_DEPRECATED) {
                    throw new \ErrorException($message, 0, $severity, $file, $line);
                }

                return false;
            }
        );

        try {
            $callback();
        } finally {
            restore_error_handler();
        }
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
