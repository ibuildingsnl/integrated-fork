<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Integrated\Bundle\ContentBundle\Controller\OEmbedController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class OEmbedControllerTest extends TestCase
{
    public function testExtractRequestedUrlDoesNotDecodeSymfonyQueryValueTwice(): void
    {
        $controller = new OEmbedController('', '', '', '');
        $request = Request::create('/oembed?url='.rawurlencode('https://example.com/foo?a=b%2Bc&x=1+2'));

        $method = new \ReflectionMethod(OEmbedController::class, 'extractRequestedUrl');
        $method->setAccessible(true);

        self::assertSame(
            'https://example.com/foo?a=b%2Bc&x=1+2',
            $method->invoke($controller, $request)
        );
    }
}
