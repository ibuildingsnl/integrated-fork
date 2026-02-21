<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Services;

class ContentTypeControllerManager
{
    /**
     * @var array<string, array{serviceId: string, class: string, actions: string[]}>
     */
    private $controllers = [];

    public function __construct(array $controllers = [])
    {
        foreach ($controllers as $content => $controller) {
            $this->addController($content, $controller['serviceId'], $controller['class'], $controller['actions']);
        }
    }

    private function addController(string $content, string $service, string $class, array $actions): void
    {
        $actions = array_filter($actions);

        if (!$actions) {
            throw new \InvalidArgumentException(\sprintf('there are no controller actions defined for the content class %s', $content));
        }

        $this->controllers[$content] = [
            'serviceId' => $service,
            'class' => $class,
            'actions' => $actions,
        ];
    }

    /**
     * @param class-string<\Integrated\Bundle\ContentBundle\Document\Content\Content> $content
     *
     * @return array{serviceId: string, class: string, actions: string[]}|null
     */
    public function getController(string $content): ?array
    {
        return $this->controllers[$content] ?? null;
    }
}
