<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Form\EventListener;

use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Form\Type\LayoutChoiceType;
use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Bundle\PageBundle\Services\ContentTypeControllerManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ContentTypePageListener implements EventSubscriberInterface
{
    private ContentTypeControllerManager $manager;
    private ThemeResolver $resolver;

    public function __construct(ContentTypeControllerManager $manager, ThemeResolver $resolver)
    {
        $this->manager = $manager;
        $this->resolver = $resolver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }

    public function preSetData(FormEvent $event): void
    {
        $contentTypePage = $event->getData();

        if (!$contentTypePage instanceof ContentTypePage) {
            return;
        }

        $controller = $this->manager->getController($class = $contentTypePage->getContentType()->getClass());

        if (!$controller) {
            throw new \Exception(\sprintf('Controller service for class "%s" is not defined', $class));
        }

        $contentTypePage->setControllerService($controller['serviceId']);

        if (!preg_match('/Content\\\(.+)Controller$/', $controller['class'], $match)) {
            throw new \InvalidArgumentException(\sprintf('The %s class is not a contentTypeController class (the namespace must contain Controller\Content and the class name must end with Controller)', $controller['class']));
        }

        $form = $event->getForm();

        if (\count($controller['actions']) > 1) {
            $form->add('controller_action', ChoiceType::class, [
                'choices' => array_combine($controller['actions'], $controller['actions']),
            ]);
        } else {
            $contentTypePage->setControllerAction($controller['actions'][0]);
        }

        $form->add('layout', LayoutChoiceType::class, [
            'theme' => $this->resolver->getTheme($contentTypePage->getChannel()),
            'directory' => strtolower(\sprintf('/content/%s/%s', $match[1], $contentTypePage->getControllerAction())),
        ]);
    }
}
