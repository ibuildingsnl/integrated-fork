<?php

namespace Integrated\Bundle\ContentHistoryBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentHistoryBundle\Controller\ContentHistoryController;
use Integrated\Bundle\ContentHistoryBundle\History\Parser;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

class ContentHistoryControllerTest extends TestCase
{
    public function testFilterReadableRowsHidesTechnicalAndNoOpRows(): void
    {
        $controller = $this->createController();
        $rows = [
            ['name' => 'relations > relationType', 'old' => 'embedded', 'new' => 'embedded'],
            ['name' => 'title', 'old' => 'Same', 'new' => 'Same'],
            ['name' => 'title', 'old' => 'Old', 'new' => 'New'],
        ];

        [$filtered, $hidden] = $this->invokePrivate($controller, 'filterReadableRows', [$rows, false]);

        self::assertCount(1, $filtered);
        self::assertSame('title', $filtered[0]['name']);
        self::assertSame(2, $hidden);
    }

    public function testApplyInlineDiffForTextRowAddsMarkedHtml(): void
    {
        $controller = $this->createController();
        $row = [
            'name' => 'content',
            'old' => 'This is a long old sentence that differs significantly for diff rendering',
            'new' => 'This is a long new sentence that differs significantly for diff rendering',
            'oldDisplay' => ['type' => 'text', 'text' => ''],
            'newDisplay' => ['type' => 'text', 'text' => ''],
        ];

        $result = $this->invokePrivate($controller, 'applyInlineDiffForTextRow', [$row]);

        self::assertSame('html', $result['oldDisplay']['type']);
        self::assertSame('html', $result['newDisplay']['type']);
        self::assertStringContainsString('history-diff-del', $result['oldDisplay']['html']);
        self::assertStringContainsString('history-diff-add', $result['newDisplay']['html']);
    }

    public function testHumanizeHistoryFieldNameMapsKnownFields(): void
    {
        $controller = $this->createController();

        $label = $this->invokePrivate($controller, 'humanizeHistoryFieldName', ['publishTime > startDate']);

        self::assertSame('Publication > Publication date', $label);
    }

    public function testFormatHistoryValueBuildsStackForMixedReferences(): void
    {
        $controller = $this->createController();
        $value = 'Featured image: Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image #735e8b13a39f33c7af4acf2ecaa001e4, Authors: Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Person #c6aef12638e33663848e6a7c5e92e0bb';

        $result = $this->invokePrivate($controller, 'formatHistoryValue', [$value]);

        self::assertSame('stack', $result['type']);
        self::assertCount(2, $result['items']);
        self::assertSame('reference', $result['items'][0]['type']);
        self::assertSame('Featured image: Image #735e8b13a39f33c7af4acf2ecaa001e4', $result['items'][0]['text']);
        self::assertSame('reference', $result['items'][1]['type']);
        self::assertSame('Authors: Person #c6aef12638e33663848e6a7c5e92e0bb', $result['items'][1]['text']);
    }

    public function testFormatHistoryValueRemovesWrongImageContextLabelForPersonReference(): void
    {
        $controller = $this->createController();
        $value = 'Featured image: Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Person #c6aef12638e33663848e6a7c5e92e0bb';

        $result = $this->invokePrivate($controller, 'formatHistoryValue', [$value]);

        self::assertSame('reference', $result['type']);
        self::assertSame('Person #c6aef12638e33663848e6a7c5e92e0bb', $result['text']);
    }

    public function testNormalizeSnapshotForCompareAlignsRelationsByRelationId(): void
    {
        $controller = $this->createController();
        $snapshot = [
            'relations' => [
                [
                    'relationId' => '__featured_image',
                    'relationType' => 'embedded',
                    'references' => [
                        ['$id' => 'img-id', 'class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image'],
                    ],
                ],
                [
                    'relationId' => '__authors',
                    'relationType' => 'author',
                    'references' => [
                        ['$id' => 'person-id', 'class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Person'],
                    ],
                ],
            ],
        ];

        $normalized = $this->invokePrivate($controller, 'normalizeSnapshotForCompare', [$snapshot]);

        self::assertArrayHasKey('relations', $normalized);
        self::assertArrayHasKey('__authors', $normalized['relations']);
        self::assertArrayHasKey('__featured_image', $normalized['relations']);
        self::assertArrayHasKey('Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Person#person-id', $normalized['relations']['__authors']['references']);
        self::assertArrayHasKey('Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image#img-id', $normalized['relations']['__featured_image']['references']);
    }

    private function createController(): ContentHistoryController
    {
        return new ContentHistoryController(
            $this->createMock(DocumentManager::class),
            new Parser(),
            $this->createMock(PaginatorInterface::class),
            $this->createMock(ContentTypeManager::class)
        );
    }

    private function invokePrivate(object $instance, string $method, array $args = []): mixed
    {
        $reflection = new \ReflectionClass($instance);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        return $target->invokeArgs($instance, $args);
    }
}
