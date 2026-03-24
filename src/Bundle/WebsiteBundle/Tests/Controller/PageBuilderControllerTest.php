<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\PageBuilder\V2\Validation\LayoutPayloadValidator;
use Integrated\Bundle\WebsiteBundle\Controller\PageBuilderController;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class PageBuilderControllerTest extends TestCase
{
    /** @var DocumentManager&MockObject */
    private DocumentManager $documentManager;
    private LayoutPayloadValidator $validator;
    /** @var ChannelContextInterface&MockObject */
    private ChannelContextInterface $channelContext;
    /** @var ChannelManagerInterface&MockObject */
    private ChannelManagerInterface $channelManager;

    protected function setUp(): void
    {
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->validator = new LayoutPayloadValidator(
            __DIR__.'/../../../PageBundle/Resources/schema/pagebuilder/v2'
        );
        $this->channelContext = $this->createMock(ChannelContextInterface::class);
        $this->channelManager = $this->createMock(ChannelManagerInterface::class);
    }

    public function testRejectsInvalidPayloadWith422(): void
    {
        $page = $this->createMock(AbstractPage::class);
        $page->method('getLayoutMeta')->willReturn([]);
        $this->mockPageRepositoryFind($page);
        $this->documentManager->expects($this->never())->method('flush');

        $controller = $this->createController();
        $response = $controller->save(Request::create(
            '/pagebuilder/save',
            'POST',
            [],
            [],
            [],
            [],
            (string) json_encode([
                'page' => 'page-id',
                'payload' => ['components' => []],
            ])
        ));

        self::assertSame(422, $response->getStatusCode());
        $decoded = json_decode((string) $response->getContent(), true);
        self::assertSame(false, $decoded['success']);
        self::assertSame('/root', $decoded['errors'][0]['path']);
        self::assertSame('required', $decoded['errors'][0]['code']);
    }

    public function testRejectsConflictingRevisionWith409(): void
    {
        $page = $this->createMock(AbstractPage::class);
        $page->method('getLayoutMeta')->willReturn(['revision' => 3]);
        $this->mockPageRepositoryFind($page);
        $this->documentManager->expects($this->never())->method('flush');

        $controller = $this->createController();
        $response = $controller->save(Request::create(
            '/pagebuilder/save',
            'POST',
            [],
            [],
            [],
            [],
            (string) json_encode([
                'page' => 'page-id',
                'expectedRevision' => 2,
                'payload' => [
                    'root' => [
                        'type' => 'container',
                        'children' => [],
                    ],
                ],
            ])
        ));

        self::assertSame(409, $response->getStatusCode());
        $decoded = json_decode((string) $response->getContent(), true);
        self::assertSame(false, $decoded['success']);
        self::assertSame(true, $decoded['conflict']);
        self::assertSame(3, $decoded['currentRevision']);
    }

    public function testSuccessfulSaveIncrementsRevisionAndReturnsIt(): void
    {
        $page = $this->createMock(AbstractPage::class);
        $page->method('getLayoutMeta')->willReturn(['revision' => 4]);
        $page->method('getLegacy')->willReturn([]);
        $page->method('getGrids')->willReturn([]);
        $page->expects($this->once())->method('setLayoutVersion')->with(2)->willReturnSelf();
        $page->expects($this->once())->method('setLayoutPayload')->willReturnSelf();
        $page->expects($this->once())->method('setLayoutMeta')->with($this->callback(function (array $meta): bool {
            return isset($meta['revision']) && $meta['revision'] === 5;
        }))->willReturnSelf();
        $page->expects($this->once())->method('setLegacy')->willReturnSelf();
        $this->mockPageRepositoryFind($page);

        $this->documentManager->expects($this->once())->method('flush');

        $controller = $this->createController();
        $response = $controller->save(Request::create(
            '/pagebuilder/save',
            'POST',
            [],
            [],
            [],
            [],
            (string) json_encode([
                'page' => 'page-id',
                'expectedRevision' => 4,
                'layoutVersion' => 2,
                'payload' => [
                    'root' => [
                        'type' => 'container',
                        'children' => [],
                    ],
                ],
                'meta' => [
                    'source' => 'website-editor',
                ],
            ])
        ));

        self::assertSame(200, $response->getStatusCode());
        $decoded = json_decode((string) $response->getContent(), true);
        self::assertSame(true, $decoded['success']);
        self::assertSame(5, $decoded['revision']);
    }

    public function testSaveSectionPresetPersistsPresetOnCurrentChannel(): void
    {
        $channel = new Channel();
        $channel->setId('website-a');
        $channel->setName('Website A');

        $this->channelContext->method('getChannel')->willReturn($channel);
        $this->channelManager->expects($this->once())->method('persist')->with($channel, true);

        $controller = $this->createController();
        $response = $controller->saveSectionPreset(Request::create(
            '/pagebuilder/section-presets',
            'POST',
            [],
            [],
            [],
            [],
            (string) json_encode([
                'name' => 'Homepage Hero',
                'html' => '<div class="row" data-block-type="row"></div>',
            ])
        ));

        self::assertSame(200, $response->getStatusCode());
        $decoded = json_decode((string) $response->getContent(), true);
        self::assertSame(true, $decoded['success']);
        self::assertNotEmpty($decoded['preset']['id'] ?? '');
        self::assertSame('Homepage Hero', $decoded['preset']['name']);

        $presets = (array) $channel->getOption('pagebuilder_section_presets');
        self::assertCount(1, $presets);
        self::assertSame('Homepage Hero', (string) ($presets[0]['name'] ?? ''));
    }

    public function testSaveSectionPresetRejectsOversizedHtml(): void
    {
        $channel = new Channel();
        $channel->setId('website-a');
        $channel->setName('Website A');

        $this->channelContext->method('getChannel')->willReturn($channel);
        $this->channelManager->expects($this->never())->method('persist');

        $controller = $this->createController();
        $response = $controller->saveSectionPreset(Request::create(
            '/pagebuilder/section-presets',
            'POST',
            [],
            [],
            [],
            [],
            (string) json_encode([
                'name' => 'Too large',
                'html' => str_repeat('x', 500001),
            ])
        ));

        self::assertSame(413, $response->getStatusCode());
        $decoded = json_decode((string) $response->getContent(), true);
        self::assertSame(false, $decoded['success']);
        self::assertSame('Preset html exceeds maximum size', $decoded['error']);
    }

    public function testSaveSectionPresetReturnsJsonErrorWhenPersistFails(): void
    {
        $channel = new Channel();
        $channel->setId('website-a');
        $channel->setName('Website A');

        $this->channelContext->method('getChannel')->willReturn($channel);
        $this->channelManager->expects($this->once())
            ->method('persist')
            ->willThrowException(new \RuntimeException('database write failed'));

        $controller = $this->createController();
        $response = $controller->saveSectionPreset(Request::create(
            '/pagebuilder/section-presets',
            'POST',
            [],
            [],
            [],
            [],
            (string) json_encode([
                'name' => 'Preset',
                'html' => '<div class="row" data-block-type="row"></div>',
            ])
        ));

        self::assertSame(500, $response->getStatusCode());
        $decoded = json_decode((string) $response->getContent(), true);
        self::assertSame(false, $decoded['success']);
        self::assertSame('Unable to persist section preset', $decoded['error']);
        self::assertSame('database write failed', $decoded['details']);
    }

    private function createController(): PageBuilderController
    {
        return new class($this->documentManager, $this->validator, $this->channelContext, $this->channelManager) extends PageBuilderController {
            protected function isGranted(mixed $attribute, mixed $subject = null): bool
            {
                return true;
            }
        };
    }

    private function mockPageRepositoryFind(?AbstractPage $page): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with('page-id')
            ->willReturn($page);

        $this->documentManager->method('getRepository')
            ->with(AbstractPage::class)
            ->willReturn($repository);
    }
}
