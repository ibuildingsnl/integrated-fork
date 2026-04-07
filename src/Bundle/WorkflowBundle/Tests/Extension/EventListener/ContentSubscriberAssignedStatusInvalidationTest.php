<?php

declare(strict_types=1);

namespace Integrated\Bundle\WorkflowBundle\Tests\Extension\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Integrated\Bundle\ContentBundle\Services\AssignedStatusCacheInvalidator;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
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

final class ContentSubscriberAssignedStatusInvalidationTest extends TestCase
{
    public function testPostUpdateInvalidatesAssignedStatusForCurrentAssignee(): void
    {
        $assigned = $this->createUser('assigned-user');

        $workflowState = new State();
        $workflowState->setAssigned($assigned);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $invalidator = $this->createMock(AssignedStatusCacheInvalidator::class);
        $invalidator
            ->expects(self::once())
            ->method('invalidateUsers')
            ->with(['assigned-user']);

        $subscriber = $this->createSubscriber($entityManager, $invalidator, $workflowState);

        $content = $this->createMock(ContentInterface::class);
        $event = new ContentEvent($content);
        $event->setData([
            'comment' => '',
            'state' => null,
            'assigned' => $assigned,
            'deadline' => null,
        ]);

        $subscriber->postUpdate($event);
    }

    private function createSubscriber(
        EntityManagerInterface $entityManager,
        AssignedStatusCacheInvalidator $assignedStatusCacheInvalidator,
        ?State $state,
    ): ContentSubscriber {
        $workflow = $this->createMock(Definition::class);

        return new class($this->createStub(UserManagerInterface::class), $this->createStub(EventDispatcherInterface::class), $this->createStub(TokenStorageInterface::class), $this->createStub(ResolverInterface::class), $entityManager, $this->createStub(DocumentManager::class), $this->createStub(MailerInterface::class), $this->createStub(RouterInterface::class), $this->createStub(ThemeManager::class), $assignedStatusCacheInvalidator, 'noreply@example.test', $this->createStub(RequestStack::class), $workflow, $state) extends ContentSubscriber {
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

    private function createUser(string $id): User
    {
        $user = new User();

        $reflection = new \ReflectionProperty(User::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($user, $id);

        return $user;
    }
}
