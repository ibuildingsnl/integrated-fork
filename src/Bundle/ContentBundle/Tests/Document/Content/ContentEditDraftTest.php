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
        $draft->pushVersion(['title' => 'Draft title']);

        self::assertSame('content-1', $draft->getContentId());
        self::assertSame('user-1', $draft->getUserId());
        self::assertSame('Draft title', $draft->getPayload()['title']);
        self::assertCount(1, $draft->getVersions());
        self::assertSame('Draft title', $draft->getVersions()[0]->getPayload()['title']);
    }

    public function testPruneVersionsAppliesAgeAndLimit(): void
    {
        $draft = new ContentEditDraft('content-1', 'user-1');
        $draft->pushVersion(['title' => 'v1'], 50);
        $draft->pushVersion(['title' => 'v2'], 50);
        $draft->pushVersion(['title' => 'v3'], 50);

        $versions = $draft->getVersions();
        $versions[2]->setSavedAt(new \DateTime('-100 days'));
        $draft->setVersions($versions);

        $removed = $draft->pruneVersions(new \DateTimeImmutable('-30 days'), 2);

        self::assertSame(1, $removed);
        self::assertCount(2, $draft->getVersions());
        self::assertSame('v3', $draft->getVersions()[0]->getPayload()['title']);
        self::assertSame('v2', $draft->getVersions()[1]->getPayload()['title']);
    }
}
