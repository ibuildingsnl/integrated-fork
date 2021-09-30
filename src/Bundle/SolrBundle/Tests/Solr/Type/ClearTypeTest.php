<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SolrBundle\Tests\Solr\Type;

use PHPUnit\Framework\TestCase;
use Integrated\Common\Converter\Type\TypeInterface;
use Integrated\Common\Converter\ContainerInterface;
use stdClass;
use Integrated\Bundle\SolrBundle\Solr\Type\ClearType;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ClearTypeTest extends TestCase
{
    public function testInterface()
    {
        self::assertInstanceOf(TypeInterface::class, $this->getInstance());
    }

    public function testBuild()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('clear');

        $this->getInstance()->build($container, new stdClass());
    }

    public function testGetName()
    {
        self::assertEquals('integrated.clear', $this->getInstance()->getName());
    }

    /**
     * @return ClearType
     */
    protected function getInstance()
    {
        return new ClearType();
    }
}
