<?php

namespace Integrated\Bundle\WorkflowBundle\Tests\Extension\EventListener;

use Doctrine\DBAL\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\WorkflowBundle\Entity\Definition\State;
use Integrated\Bundle\WorkflowBundle\Extension\EventListener\ContentSubscriber;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\ContentType\ResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ContentSubscriberStateDataTest extends TestCase
{
    public function testGetStateDataUsesUuidStateIdWithoutCastingToInteger(): void
    {
        $uuid = '41e98bed-5008-11e7-8199-089e013951df';
        $content = $this->createMock(ContentInterface::class);
        $content->expects(self::once())->method('getId')->willReturn('content-123');

        $state = new State();

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('fetchAssociative')
            ->willReturn([
                'state_id' => $uuid,
                'assigned_id' => null,
                'assigned_class' => null,
                'deadline' => null,
            ]);

        $stateRepository = $this->createMock(EntityRepository::class);
        $stateRepository->expects(self::once())
            ->method('find')
            ->with($uuid)
            ->willReturn($state);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('getConnection')->willReturn($connection);
        $entityManager->expects(self::once())
            ->method('getRepository')
            ->with(State::class)
            ->willReturn($stateRepository);

        $subscriber = new class($this->createStub(UserManagerInterface::class), $this->createStub(EventDispatcherInterface::class), $this->createStub(TokenStorageInterface::class), $this->createStub(ResolverInterface::class), $entityManager, $this->createStub(DocumentManager::class), $this->createStub(MailerInterface::class), $this->createStub(RouterInterface::class), $this->createStub(ThemeManager::class), 'noreply@example.test', $this->createStub(RequestStack::class)) extends ContentSubscriber {
            /**
             * @return array{state: State, assigned: string|null, deadline: \DateTimeInterface|null}|null
             */
            public function readStateData(ContentInterface $content): ?array
            {
                return $this->getStateData($content);
            }
        };

        $data = $subscriber->readStateData($content);

        self::assertIsArray($data);
        self::assertSame($state, $data['state']);
        self::assertNull($data['assigned']);
        self::assertNull($data['deadline']);
    }
}
