<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;

final class OEmbedVendorAdapterTest extends TestCase
{
    public function testFacebookAdapterUsesCurrentGraphApiEndpoints(): void
    {
        self::assertMatchesRegularExpression(
            '#^https://graph\.facebook\.com/v\d+\.\d+/oembed_page$#',
            \Embed\Adapters\Facebook\OEmbed::ENDPOINT_PAGE
        );
        self::assertMatchesRegularExpression(
            '#^https://graph\.facebook\.com/v\d+\.\d+/oembed_post$#',
            \Embed\Adapters\Facebook\OEmbed::ENDPOINT_POST
        );
        self::assertMatchesRegularExpression(
            '#^https://graph\.facebook\.com/v\d+\.\d+/oembed_video$#',
            \Embed\Adapters\Facebook\OEmbed::ENDPOINT_VIDEO
        );
    }

    public function testInstagramAdapterUsesCurrentGraphApiEndpoint(): void
    {
        self::assertMatchesRegularExpression(
            '#^https://graph\.facebook\.com/v\d+\.\d+/instagram_oembed$#',
            \Embed\Adapters\Instagram\OEmbed::ENDPOINT
        );
    }
}
