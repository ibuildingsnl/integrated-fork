<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Services;

use Integrated\Bundle\PageBundle\Services\PageCopy\PageCopyRequestFactory;
use PHPUnit\Framework\TestCase;

final class PageCopyRequestFactoryTest extends TestCase
{
    public function testCreatesTypedRequestForSelectedPagesOnly(): void
    {
        $factory = new PageCopyRequestFactory();

        $request = $factory->createFromFormData([
            'sourceChannel' => ' source ',
            'targetChannel' => ' target ',
            'pages' => [
                'pagepage-a' => [
                    'selected' => true,
                    'blocks' => [
                        'block_alpha' => [
                            'operation' => '',
                            'newBlockId' => '',
                        ],
                        'block_beta' => [
                            'operation' => 'clone',
                            'newBlockId' => ' target-beta ',
                        ],
                    ],
                ],
                'pagepage-b' => [
                    'selected' => false,
                    'blocks' => [
                        'block_gamma' => [
                            'operation' => 'clone',
                            'newBlockId' => 'target-gamma',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertSame('source', $request->getSourceChannelId());
        self::assertSame('target', $request->getTargetChannelId());
        self::assertCount(1, $request->getPageInstructions());

        $pageInstruction = $request->getPageInstruction('page-a');
        self::assertNotNull($pageInstruction);
        $reuseInstruction = $pageInstruction->getBlockInstruction('alpha');
        $cloneInstruction = $pageInstruction->getBlockInstruction('beta');

        self::assertNotNull($reuseInstruction);
        self::assertNotNull($cloneInstruction);
        self::assertCount(2, $pageInstruction->getBlockInstructions());
        self::assertFalse($reuseInstruction->isClone());
        self::assertTrue($cloneInstruction->isClone());
        self::assertSame('target-beta', $cloneInstruction->getTargetBlockId());
        self::assertNull($request->getPageInstruction('page-b'));
    }

    public function testCloneOperationRequiresNewBlockIdDuringNormalization(): void
    {
        $factory = new PageCopyRequestFactory();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing target block id');

        $factory->createFromFormData([
            'sourceChannel' => 'source',
            'targetChannel' => 'target',
            'pages' => [
                'pagepage-a' => [
                    'selected' => true,
                    'blocks' => [
                        'block_alpha' => [
                            'operation' => 'clone',
                            'newBlockId' => ' ',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
