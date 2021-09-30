<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Menu;

use Knp\Menu\ItemInterface;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use ReflectionClass;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Common\Security\PermissionInterface;
use Integrated\Common\ContentType\IteratorInterface;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ContentTypeMenuBuilder
{
    /**
     * @var string
     */
    public const CONTENT_CLASS = 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Content';

    /**
     * @var string
     */
    public const CONTENT_TYPE_CLASS = 'Integrated\\Bundle\\ContentBundle\\Document\\ContentType\\ContentType';

    /**
     * @var string
     */
    public const ROUTE = 'integrated_content_content_new';

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var ContentTypeManager
     */
    protected $contentTypeManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @param FactoryInterface              $factory
     * @param ContentTypeManager            $contentTypeManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        FactoryInterface $factory,
        ContentTypeManager $contentTypeManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->factory = $factory;
        $this->contentTypeManager = $contentTypeManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @return ItemInterface
     */
    public function createMenu()
    {
        $menu = $this->factory->createItem('root');

        $contentTypes = $this->contentTypeManager->getAll();

        foreach ($this->group($contentTypes) as $key => $documents) {
            $child = $menu->addChild($key);
            $hasItems = false;

            /** @var ContentType $document */
            foreach ($documents as $document) {
                if (!$this->authorizationChecker->isGranted(PermissionInterface::WRITE, $document)) {
                    continue;
                }

                $child->addChild(
                    $document->getName(),
                    ['route' => self::ROUTE, 'routeParameters' => ['type' => $document->getId()]]
                );

                $hasItems = true;
            }

            if (!$hasItems) {
                $menu->removeChild($key);
            }
        }

        return $menu;
    }

    /**
     * @param IteratorInterface $result
     *
     * @return array
     */
    protected function group(IteratorInterface $result)
    {
        $menu = [];

        /** @var ContentType $document */
        foreach ($result as $document) {
            if (!is_a($document, self::CONTENT_TYPE_CLASS)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($document->getClass());

            if ($parent = $this->getParentClass($reflectionClass)) {
                $menu[$parent->getShortName()][] = $document;
            } else {
                $menu[$reflectionClass->getShortName()][] = $document;
            }
        }

        ksort($menu);

        return $menu;
    }

    /**
     * @param ReflectionClass $reflectionClass
     *
     * @return bool|ReflectionClass
     */
    protected function getParentClass(ReflectionClass $reflectionClass)
    {
        if ($parent = $reflectionClass->getParentClass()) {
            if ($parent->getName() === self::CONTENT_CLASS) {
                return $reflectionClass;
            }

            return $this->getParentClass($parent);
        }

        return false;
    }
}
