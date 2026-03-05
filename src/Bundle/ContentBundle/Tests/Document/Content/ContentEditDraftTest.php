<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Document\Content;

use Integrated\Bundle\ContentBundle\Document\Content\ContentEditDraft;
use PHPUnit\Framework\TestCase;

class ContentEditDraftTest extends TestCase
{
    public function testDraftStoresPayloadAndMetadata(): void
    {
        $draft = new ContentEditDraft('content-1', 'user-1');
        $draft->setPayload(['title' => 'Draft title']);

        self::assertSame('content-1', $draft->getContentId());
        self::assertSame('user-1', $draft->getUserId());
        self::assertSame('Draft title', $draft->getPayload()['title']);
    }
}

