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

        $actionChoices = $this->getActionChoices($controller['actions']);

        if (\count($actionChoices) > 1) {
            $form->add('controller_action', ChoiceType::class, [
                'choices' => $actionChoices,
            ]);
        } else {
            $contentTypePage->setControllerAction((string) reset($actionChoices));
        }

        $form->add('layout', LayoutChoiceType::class, [
            'theme' => $this->resolver->getTheme($contentTypePage->getChannel()),
            'directory' => strtolower(\sprintf('/content/%s/%s', $match[1], $this->normalizeAction($contentTypePage->getControllerAction()))),
        ]);
    }

    private function normalizeAction(?string $action): string
    {
        $action = trim((string) $action);

        if ('' === $action) {
            return '';
        }

        if ('__invoke' === $action) {
            return $action;
        }

        return preg_replace('/Action$/', '', $action) ?: $action;
    }

    /**
     * Build stable action choices while collapsing legacy duplicates like show/showAction.
     *
     * @param string[] $actions
     *
     * @return array<string, string>
     */
    private function getActionChoices(array $actions): array
    {
        $choices = [];

        foreach ($actions as $action) {
            if (!\is_string($action) || '' === trim($action)) {
                continue;
            }

            $action = trim($action);
            $normalized = $this->normalizeAction($action);

            if ('' === $normalized) {
                continue;
            }

            if (!isset($choices[$normalized]) || $action === $normalized) {
                $choices[$normalized] = $action;
            }
        }

        return $choices;
    }
}
