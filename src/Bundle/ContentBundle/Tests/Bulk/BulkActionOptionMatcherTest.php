<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Bulk;

use Integrated\Bundle\ContentBundle\Bulk\BulkActionOptionMatcher;
use Integrated\Common\Bulk\BulkActionInterface;
use PHPUnit\Framework\TestCase;

class BulkActionOptionMatcherTest extends TestCase
{
    public function testMatchReturnsTrueWhenHandlerAndOptionMatch(): void
    {
        $matcher = new BulkActionOptionMatcher('handler.class', 'sourceUrl');
        $action = $this->createAction('handler.class', ['sourceUrl' => 'https://example.org']);

        self::assertTrue($matcher->match($action));
    }

    public function testMatchReturnsFalseWhenHandlerDoesNotMatch(): void
    {
        $matcher = new BulkActionOptionMatcher('handler.class', 'sourceUrl');
        $action = $this->createAction('other.handler', ['sourceUrl' => 'https://example.org']);

        self::assertFalse($matcher->match($action));
    }

    public function testMatchReturnsFalseWhenOptionDoesNotExist(): void
    {
        $matcher = new BulkActionOptionMatcher('handler.class', 'sourceUrl');
        $action = $this->createAction('handler.class', ['other' => 'value']);

        self::assertFalse($matcher->match($action));
    }

    private function createAction(string $handler, array $options): BulkActionInterface
    {
        return new class($handler, $options) implements BulkActionInterface {
            private string $handler;

            private array $options;

            public function __construct(string $handler, array $options)
            {
                $this->handler = $handler;
                $this->options = $options;
            }

            public function getHandler()
            {
                return $this->handler;
            }

            public function getOptions()
            {
                return $this->options;
            }
        };
    }
}

