<?php

namespace Integrated\Bundle\SolrBundle\Tests\Solr\Type;

use Integrated\Bundle\SolrBundle\Solr\Type\JsonType;
use Integrated\Common\Converter\Container;
use PHPUnit\Framework\TestCase;

class JsonTypeTest extends TestCase
{
    public function testBuildSupportsSimpleScalarPaths(): void
    {
        $type = new JsonType();
        $container = new Container();
        $object = new class {
            public string $field = 'abc';
        };

        $type->build($container, $object, ['json' => 'field']);

        self::assertSame(['json' => ['"abc"']], $container->toArray());
    }
}
