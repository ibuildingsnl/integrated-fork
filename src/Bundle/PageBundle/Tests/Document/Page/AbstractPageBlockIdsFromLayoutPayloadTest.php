<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Document\Page;

use Integrated\Bundle\PageBundle\Document\Page\Page;
use PHPUnit\Framework\TestCase;

final class AbstractPageBlockIdsFromLayoutPayloadTest extends TestCase
{
    public function testCollectsBlockIdsFromV2Payload(): void
    {
        $page = new Page();
        $page->setLayoutVersion(2);
        $page->setLayoutPayload([
            'root' => [
                'type' => 'container',
                'children' => [
                    ['type' => 'block_ref', 'props' => ['blockId' => 'block-a']],
                    ['type' => 'block_ref', 'props' => ['blockId' => 'block-b']],
                ],
            ],
        ]);

        $page->updateBlockIdsFromLayoutPayload();

        self::assertSame(['block-a', 'block-b'], $page->getBlockIds());
    }
}

