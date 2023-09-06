<?php

namespace Integrated\Bundle\UserBundle\EventListener;

use Integrated\Bundle\UserBundle\Context\ScopeContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ScopeInjectionSubscriber implements EventSubscriberInterface
{
    /**
     * @var ScopeContext
     */
    private $context;

    public function __construct(ScopeContext $context)
    {
        $this->context = $context;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onController',
        ];
    }

    public function onController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!\is_array($controller)) {
            return;
        }

        $class = new \ReflectionClass($controller[0]::class);
        if ($class->getNamespaceName() !== 'Integrated\Bundle\UserBundle\Controller\Website') {
            return;
        }

        if (!$scope = $this->context->getScope()) {
            throw new NotFoundHttpException('Scope not found');
        }

        $event->getRequest()->attributes->set('scope', $scope);
    }
}
