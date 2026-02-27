<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\MenuBundle\Menu\DatabaseMenuFactory;
use Integrated\Bundle\MenuBundle\Provider\IntegratedMenuProvider;
use Integrated\Bundle\WebsiteBundle\Controller\MenuController;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class MenuControllerTest extends TestCase
{
    /** @var DocumentManager&MockObject */
    private DocumentManager $documentManager;
    /** @var IntegratedMenuProvider&MockObject */
    private IntegratedMenuProvider $menuProvider;
    /** @var DatabaseMenuFactory&MockObject */
    private DatabaseMenuFactory $menuFactory;
    /** @var ChannelContextInterface&MockObject */
    private ChannelContextInterface $channelContext;

    protected function setUp(): void
    {
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->menuProvider = $this->createMock(IntegratedMenuProvider::class);
        $this->menuFactory = $this->createMock(DatabaseMenuFactory::class);
        $this->channelContext = $this->createMock(ChannelContextInterface::class);
    }

    public function testSaveStripsPlaceholderItemsBeforePersistence(): void
    {
        $captured = [];
        $menu = new class {
            public ?object $channel = null;

            public function getName(): string
            {
                return 'main';
            }

            /** @return array<int, mixed> */
            public function getChildren(): array
            {
                return [];
            }

            public function setChannel(object $channel): void
            {
                $this->channel = $channel;
            }
        };
        $channel = new \stdClass();

        $this->channelContext->method('getChannel')->willReturn($channel);
        $this->menuProvider->method('has')->with('main')->willReturn(false);
        $this->menuFactory
            ->expects($this->once())
            ->method('fromArray')
            ->willReturnCallback(function (array $payload) use (&$captured, $menu) {
                $captured = $payload;

                return $menu;
            });

        $this->documentManager->expects($this->once())->method('persist')->with($menu);
        $this->documentManager->expects($this->once())->method('flush');

        $controller = $this->createController();
        $controller->save($this->createSaveRequest([
            'menu' => [[
                'name' => 'main',
                'children' => [
                    [
                        'name' => 'Real item',
                        'uri' => '/real',
                        'typeLink' => '0',
                        'children' => [],
                    ],
                    [
                        'name' => '+',
                        'uri' => '#',
                        'searchSelection' => '',
                        'children' => [],
                    ],
                ],
            ]],
        ]));

        self::assertSame('main', $captured['name']);
        self::assertCount(1, $captured['children']);
        self::assertSame('Real item', $captured['children'][0]['name']);
        self::assertSame($channel, $menu->channel);
    }

    public function testSaveKeepsHeadingItemWithoutUrl(): void
    {
        $captured = [];
        $menu = new class {
            public ?object $channel = null;

            public function getName(): string
            {
                return 'main';
            }

            /** @return array<int, mixed> */
            public function getChildren(): array
            {
                return [];
            }

            public function setChannel(object $channel): void
            {
                $this->channel = $channel;
            }
        };
        $channel = new \stdClass();

        $this->channelContext->method('getChannel')->willReturn($channel);
        $this->menuProvider->method('has')->with('main')->willReturn(false);
        $this->menuFactory
            ->expects($this->once())
            ->method('fromArray')
            ->willReturnCallback(function (array $payload) use (&$captured, $menu) {
                $captured = $payload;

                return $menu;
            });

        $this->documentManager->expects($this->once())->method('persist')->with($menu);
        $this->documentManager->expects($this->once())->method('flush');

        $controller = $this->createController();
        $controller->save($this->createSaveRequest([
            'menu' => [[
                'name' => 'main',
                'children' => [
                    [
                        'name' => 'Section heading',
                        'typeLink' => '2',
                        'uri' => '',
                        'searchSelection' => '',
                        'children' => [],
                    ],
                ],
            ]],
        ]));

        self::assertCount(1, $captured['children']);
        self::assertSame('Section heading', $captured['children'][0]['name']);
        self::assertSame('2', (string) $captured['children'][0]['typeLink']);
        self::assertSame('', (string) $captured['children'][0]['uri']);
        self::assertSame($channel, $menu->channel);
    }

    /** @param array<string, mixed> $payload */
    private function createSaveRequest(array $payload): Request
    {
        return Request::create('/menu/save', 'POST', [], [], [], [], (string) json_encode($payload));
    }

    private function createController(): MenuController
    {
        return new class($this->documentManager, $this->menuProvider, $this->menuFactory, $this->channelContext) extends MenuController {
            protected function isGranted(mixed $attribute, mixed $subject = null): bool
            {
                return true;
            }
        };
    }
}
