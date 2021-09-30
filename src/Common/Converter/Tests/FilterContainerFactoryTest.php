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
use Integrated\Common\Converter\FilterContainer;
use Integrated\Common\Converter\FilterContainerFactory;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class FilterContainerFactoryTest extends TestCase
{
    public function testInterface()
    {
        self::assertInstanceOf(ContainerFactoryInterface::class, $this->getInstance());
    }

    public function testCreateContainer()
    {
        self::assertInstanceOf(FilterContainer::class, $this->getInstance()->createContainer());
    }

    /**
     * @return FilterContainerFactory
     */
    protected function getInstance()
    {
        return new FilterContainerFactory();
    }
}
