<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Converter\Tests\Type;

use PHPUnit\Framework\TestCase;
use Integrated\Common\Converter\Type\ResolvedTypeFactoryInterface;
use Integrated\Common\Converter\Type\ResolvedType;
use Integrated\Common\Converter\Type\TypeInterface;
use Integrated\Common\Converter\Type\ResolvedTypeFactory;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ResolvedTypeFactoryTest extends TestCase
{
    public function testInterface()
    {
        self::assertInstanceOf(ResolvedTypeFactoryInterface::class, $this->getInstance());
    }

    public function testCreateType()
    {
        $factory = $this->getInstance();

        self::assertInstanceOf(ResolvedType::class, $factory->createType($this->createMock(TypeInterface::class), []));
    }

    /**
     * @return ResolvedTypeFactory
     */
    protected function getInstance()
    {
        return new ResolvedTypeFactory();
    }
}
