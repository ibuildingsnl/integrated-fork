<?php

namespace Integrated\Bundle\WorkflowBundle\Tests\Extension\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Integrated\Bundle\ContentBundle\Services\AssignedStatusCacheInvalidator;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\Log;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\State;
use Integrated\Bundle\WorkflowBundle\Extension\EventListener\ContentSubscriber;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Content\Extension\Event\ContentEvent;
use Integrated\Common\ContentType\ResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ContentSubscriberDeadlineTest extends TestCase
{
    public function testPostUpdateDoesNotTreatEquivalentImmutableDeadlineAsChange(): void
    {
        $deadline = new \DateTime('2026-04-04 10:30:00');
        $existingState = new State();
        $existingState->setDeadline($deadline);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $subscriber = $this->createSubscriber($entityManager, $existingState);

        $content = $this->createMock(ContentInterface::class);
        $event = new ContentEvent($content);
        $event->setData([
            'comment' => '',
            'state' => null,
            'assigned' => null,
            'deadline' => new \DateTimeImmutable('2026-04-04 10:30:00'),
        ]);

        $subscriber->postUpdate($event);

        self::assertInstanceOf(\DateTime::class, $existingState->getDeadline());
        self::assertEquals('2026-04-04 10:30:00', $existingState->getDeadline()->format('Y-m-d H:i:s'));
    }

    public function testPostUpdateNormalizesImmutableDeadlineBeforePersistingLogAndState(): void
    {
        $existingState = new State();
        $persistedLog = null;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(function (object $entity) use (&$persistedLog): bool {
                if (!$entity instanceof Log) {
                    return false;
                }

                $persistedLog = $entity;

                return true;
            }));
        $entityManager->expects(self::once())->method('flush');

        $subscriber = $this->createSubscriber($entityManager, $existingState);

        $content = $this->createMock(ContentInterface::class);
        $event = new ContentEvent($content);
        $event->setData([
            'comment' => '',
            'state' => null,
            'assigned' => null,
            'deadline' => new \DateTimeImmutable('2026-04-05 15:45:00'),
        ]);

        $subscriber->postUpdate($event);

        self::assertInstanceOf(Log::class, $persistedLog);
        self::assertInstanceOf(\DateTime::class, $persistedLog->getDeadline());
        self::assertSame('2026-04-05 15:45:00', $persistedLog->getDeadline()->format('Y-m-d H:i:s'));
        self::assertInstanceOf(\DateTime::class, $existingState->getDeadline());
        self::assertSame('2026-04-05 15:45:00', $existingState->getDeadline()->format('Y-m-d H:i:s'));
    }

    private function createSubscriber(EntityManagerInterface $entityManager, ?State $state): ContentSubscriber
    {
        $workflow = $this->createMock(Definition::class);

        return new class($this->createStub(UserManagerInterface::class), $this->createStub(EventDispatcherInterface::class), $this->createStub(TokenStorageInterface::class), $this->createStub(ResolverInterface::class), $entityManager, $this->createStub(DocumentManager::class), $this->createStub(MailerInterface::class), $this->createStub(RouterInterface::class), $this->createStub(ThemeManager::class), $this->createStub(AssignedStatusCacheInvalidator::class), 'noreply@example.test', $this->createStub(RequestStack::class), $workflow, $state) extends ContentSubscriber {
            public function __construct(
                UserManagerInterface $userManager,
                EventDispatcherInterface $eventDispatcher,
                TokenStorageInterface $tokenStorage,
                ResolverInterface $resolver,
                EntityManagerInterface $entityManager,
                DocumentManager $documentManager,
                MailerInterface $mailer,
                RouterInterface $router,
                ThemeManager $themeManager,
                AssignedStatusCacheInvalidator $assignedStatusCacheInvalidator,
                string $fromEmail,
                RequestStack $requestStack,
                private readonly Definition $workflow,
                private ?State $workflowState,
            ) {
                parent::__construct(
                    $userManager,
                    $eventDispatcher,
                    $tokenStorage,
                    $resolver,
                    $entityManager,
                    $documentManager,
                    $mailer,
                    $router,
                    $themeManager,
                    $assignedStatusCacheInvalidator,
                    $fromEmail,
                    $requestStack
                );
            }

            protected function getWorkflow(object $object): Definition
            {
                return $this->workflow;
            }

            protected function getState(ContentInterface $content): ?State
            {
                return $this->workflowState;
            }
        };
    }
}
