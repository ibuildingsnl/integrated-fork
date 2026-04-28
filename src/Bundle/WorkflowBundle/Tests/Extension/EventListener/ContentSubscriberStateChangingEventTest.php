<?php

declare(strict_types=1);

namespace Integrated\Bundle\WorkflowBundle\Tests\Extension\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Integrated\Bundle\ContentBundle\Services\AssignedStatusCacheInvalidator;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Definition\State as DefinitionState;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\State as WorkflowState;
use Integrated\Bundle\WorkflowBundle\Extension\EventListener\ContentSubscriber;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Content\Extension\Event\ContentEvent;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Workflow\Event\WorkflowStateChangingEvent;
use Integrated\Common\Workflow\Events as WorkflowEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ContentSubscriberStateChangingEventTest extends TestCase
{
    public function testPreUpdateDispatchesCancelableStateChangingEventBeforeApplyingTargetState(): void
    {
        $content = $this->createMock(ContentInterface::class);
        $currentState = new DefinitionState();
        $targetState = new DefinitionState();

        $workflowState = new WorkflowState();
        $workflowState->setState($currentState);

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            WorkflowEvents::STATE_CHANGING,
            function (WorkflowStateChangingEvent $event) use ($content, $workflowState, $currentState, $targetState): void {
                self::assertSame($content, $event->getContent());
                self::assertSame($workflowState, $event->getWorkflowState());
                self::assertSame($currentState, $event->getCurrentState());
                self::assertSame($targetState, $event->getTargetState());

                $event->deny('External quality gate rejected this transition.');
            }
        );

        $subscriber = $this->createSubscriber($dispatcher, $workflowState);

        $event = new ContentEvent($content);
        $event->setData([
            'comment' => '',
            'state' => $targetState,
            'assigned' => null,
            'deadline' => null,
        ]);

        $subscriber->preUpdate($event);

        $data = $event->getData();

        self::assertSame($currentState, $data['state']);
        self::assertSame('External quality gate rejected this transition.', $data['workflow_state_denial_message']);
    }

    private function createSubscriber(EventDispatcherInterface $dispatcher, WorkflowState $state): ContentSubscriber
    {
        $workflow = $this->createMock(Definition::class);

        return new class($this->createStub(UserManagerInterface::class), $dispatcher, $this->createStub(TokenStorageInterface::class), $this->createStub(ResolverInterface::class), $this->createStub(EntityManagerInterface::class), $this->createStub(DocumentManager::class), $this->createStub(MailerInterface::class), $this->createStub(RouterInterface::class), $this->createStub(ThemeManager::class), $this->createStub(AssignedStatusCacheInvalidator::class), 'noreply@example.test', $this->createStub(RequestStack::class), $workflow, $state) extends ContentSubscriber {
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
                private readonly WorkflowState $workflowState,
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

            protected function getState(ContentInterface $content): WorkflowState
            {
                return $this->workflowState;
            }
        };
    }
}
