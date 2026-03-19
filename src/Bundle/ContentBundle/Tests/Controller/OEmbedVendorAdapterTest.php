<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;

final class OEmbedVendorAdapterTest extends TestCase
{
    public function testFacebookAdapterUsesCurrentGraphApiEndpoints(): void
    {
        self::assertSame('https://graph.facebook.com/v22.0/oembed_page', \Embed\Adapters\Facebook\OEmbed::ENDPOINT_PAGE);
        self::assertSame('https://graph.facebook.com/v22.0/oembed_post', \Embed\Adapters\Facebook\OEmbed::ENDPOINT_POST);
        self::assertSame('https://graph.facebook.com/v22.0/oembed_video', \Embed\Adapters\Facebook\OEmbed::ENDPOINT_VIDEO);
    }

    public function testInstagramAdapterUsesCurrentGraphApiEndpoint(): void
    {
        self::assertSame('https://graph.facebook.com/v22.0/instagram_oembed', \Embed\Adapters\Instagram\OEmbed::ENDPOINT);
    }
}
