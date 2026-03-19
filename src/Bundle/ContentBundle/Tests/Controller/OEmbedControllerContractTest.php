<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Integrated\Bundle\ContentBundle\Controller\OEmbedController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class OEmbedControllerContractTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists('Embed\\Embed', false)) {
            class_alias(FakeEmbed::class, 'Embed\\Embed');
        }

        FakeEmbed::$exception = null;
        FakeEmbed::$info = null;
        FakeEmbed::$lastSettings = null;
        FakeEmbed::$lastUrl = null;
    }

    public function testReturnsBadRequestWhenUrlParameterIsMissing(): void
    {
        $controller = new OEmbedController('fb-app', 'fb-secret', 'x-app', 'x-secret');

        $response = $controller->oEmbed(new Request());

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame(['msg' => 'No URL specified'], $this->decodeJson($response));
    }

    public function testReturnsEmbedPayloadWhenProviderReturnsCodeHtml(): void
    {
        FakeEmbed::$info = FakeEmbedInfo::withCode(
            'https://www.instagram.com/p/abc123/',
            '<blockquote>Instagram embed</blockquote>'
        );

        $controller = new OEmbedController('fb-app', 'fb-secret', 'x-app', 'x-secret');
        $response = $controller->oEmbed(new Request(['url' => rawurlencode('https://www.instagram.com/p/abc123/')]));

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('<blockquote>Instagram embed</blockquote>', $this->decodeJson($response)['code']);
    }

    public function testReturnsFacebookFallbackEmbedWhenMetaOmitsHtml(): void
    {
        FakeEmbed::$info = FakeEmbedInfo::withoutCode('https://www.facebook.com/IjssalonBastani/posts/1495329049259993');

        $controller = new OEmbedController('fb-app', 'fb-secret', 'x-app', 'x-secret');
        $response = $controller->oEmbed(
            new Request(['url' => rawurlencode('https://www.facebook.com/IjssalonBastani/posts/1495329049259993')])
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $payload = $this->decodeJson($response);
        self::assertStringContainsString('facebook.com/IjssalonBastani/posts/1495329049259993', (string) $payload['code']);
    }

    public function testReturnsUnprocessableEntityWhenMetaOmitsHtmlForInstagram(): void
    {
        FakeEmbed::$info = FakeEmbedInfo::withoutCode(
            'https://www.instagram.com/p/abc123/',
            [
                'error' => [
                    'message' => "(#10) To use 'Meta oEmbed Read', your use of this endpoint must be reviewed and approved by Facebook.",
                    'type' => 'OAuthException',
                    'code' => 10,
                    'access_token' => 'secret-token',
                ],
            ],
            'https://graph.facebook.com/v22.0/instagram_oembed?url=https%3A%2F%2Fwww.instagram.com%2Fp%2Fabc123%2F&access_token=secret-token'
        );

        $controller = new OEmbedController('fb-app', 'fb-secret', 'x-app', 'x-secret');
        $response = $controller->oEmbed(new Request(['url' => rawurlencode('https://www.instagram.com/p/abc123/')]));

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $payload = $this->decodeJson($response);

        self::assertSame(
            'Instagram oEmbed is not available for this app. Meta oEmbed Read approval is required.',
            $payload['msg']
        );
        self::assertSame('meta_oembed_permission_required', $payload['reason']);
        self::assertSame('https://www.instagram.com/p/abc123/', $payload['debug']['requested_url']);
        self::assertFalse($payload['debug']['has_code']);
        self::assertSame('Provider', $payload['debug']['provider_name']);
        self::assertStringContainsString('instagram_oembed', (string) $payload['debug']['oembed_endpoint']);
        self::assertStringNotContainsString('access_token=', (string) $payload['debug']['oembed_endpoint']);
        self::assertSame(10, $payload['debug']['oembed_data']['error']['code']);
        self::assertArrayNotHasKey('access_token', $payload['debug']['oembed_data']['error']);
    }

    public function testReturnsBadGatewayWhenProviderRequestFails(): void
    {
        FakeEmbed::$exception = new \RuntimeException('Meta upstream failed');

        $controller = new OEmbedController('fb-app', 'fb-secret', 'x-app', 'x-secret');
        $response = $controller->oEmbed(
            new Request(['url' => rawurlencode('https://www.facebook.com/IjssalonBastani/posts/1495329049259993')])
        );

        self::assertSame(Response::HTTP_BAD_GATEWAY, $response->getStatusCode());
        self::assertSame([
            'msg' => 'Unable to fetch embed data from provider.',
        ], $this->decodeJson($response));
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(Response $response): array
    {
        $decoded = json_decode((string) $response->getContent(), true);
        self::assertIsArray($decoded);

        return $decoded;
    }
}

final class FakeEmbed
{
    /** @var array<string, mixed>|null */
    public static ?array $lastSettings = null;
    public static ?string $lastUrl = null;
    public static ?\Throwable $exception = null;
    public static ?FakeEmbedInfo $info = null;

    /**
     * @param array<string, mixed> $settings
     */
    public function setSettings(array $settings): void
    {
        self::$lastSettings = $settings;
    }

    public function get(string $url): FakeEmbedInfo
    {
        self::$lastUrl = $url;

        if (self::$exception instanceof \Throwable) {
            throw self::$exception;
        }

        if (self::$info instanceof FakeEmbedInfo) {
            return self::$info;
        }

        return FakeEmbedInfo::withoutCode($url);
    }
}

final class FakeEmbedInfo
{
    public string $title = 'Title';
    public string $description = 'Description';
    public string $url;
    /** @var array<int, string> */
    public array $keywords = ['tag'];
    public ?string $image = 'https://example.test/image.jpg';
    public ?object $code;
    public string $authorName = 'Author';
    public string $authorUrl = 'https://example.test/author';
    public string $providerName = 'Provider';
    public string $providerUrl = 'https://example.test/provider';
    public ?string $icon = 'https://example.test/icon.png';
    public ?string $favicon = 'https://example.test/favicon.ico';
    public ?string $publishedTime = '2026-03-19T12:00:00+00:00';
    public ?string $license = 'https://example.test/license';
    /** @var array<int, string> */
    public array $feeds = ['https://example.test/feed.xml'];
    private FakeOEmbedData $oEmbed;

    private function __construct(string $url, ?object $code, array $oEmbedData = [], ?string $oEmbedEndpoint = null)
    {
        $this->url = $url;
        $this->code = $code;
        $this->oEmbed = new FakeOEmbedData($oEmbedData, $oEmbedEndpoint);
    }

    public static function withCode(string $url, string $html): self
    {
        return new self($url, (object) [
            'html' => $html,
            'width' => 500,
            'height' => 400,
            'ratio' => 1.25,
        ]);
    }

    public static function withoutCode(string $url, array $oEmbedData = [], ?string $oEmbedEndpoint = null): self
    {
        return new self($url, null, $oEmbedData, $oEmbedEndpoint);
    }

    public function getOEmbed(): FakeOEmbedData
    {
        return $this->oEmbed;
    }
}

final class FakeOEmbedData
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(
        private array $data,
        private ?string $endpoint,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }
}
