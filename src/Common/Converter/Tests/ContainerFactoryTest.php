<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Converter\Tests;

use PHPUnit\Framework\TestCase;
use Integrated\Common\Converter\ContainerFactoryInterface;
use Integrated\Common\Converter\Container;
use Integrated\Common\Converter\ContainerFactory;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ContainerFactoryTest extends TestCase
{
    public function testInterface()
    {
        self::assertInstanceOf(ContainerFactoryInterface::class, $this->getInstance());
    }

    public function testCreateContainer()
    {
        self::assertInstanceOf(Container::class, $this->getInstance()->createContainer());
    }

    /**
     * @return ContainerFactory
     */
    protected function getInstance()
    {
        return new ContainerFactory();
    }
}
