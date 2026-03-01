<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\PageBuilder\V2;

use Integrated\Bundle\PageBundle\PageBuilder\V2\Validation\LayoutPayloadValidator;
use PHPUnit\Framework\TestCase;

final class LayoutPayloadValidatorTest extends TestCase
{
    private LayoutPayloadValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new LayoutPayloadValidator(__DIR__.'/../../../Resources/schema/pagebuilder/v2');
    }

    public function testRejectsPayloadWithoutRootNode(): void
    {
        $errors = $this->validator->validate(['components' => []], 'twindigital');

        self::assertNotEmpty($errors);
        self::assertSame('/root', $errors[0]->path);
        self::assertSame('required', $errors[0]->code);
    }

    public function testRejectsRootThatIsNotContainer(): void
    {
        $errors = $this->validator->validate([
            'root' => [
                'type' => 'block_ref',
                'props' => ['blockId' => 'block-a'],
            ],
        ], 'twindigital');

        self::assertNotEmpty($errors);
        self::assertSame('/root/type', $errors[0]->path);
        self::assertSame('invalid_type', $errors[0]->code);
    }

    public function testRejectsBlockReferenceWithoutBlockId(): void
    {
        $errors = $this->validator->validate([
            'root' => [
                'type' => 'container',
                'children' => [
                    [
                        'type' => 'block_ref',
                        'props' => [],
                    ],
                ],
            ],
        ], 'twindigital');

        self::assertNotEmpty($errors);
        self::assertSame('/root/children/0/props/blockId', $errors[0]->path);
        self::assertSame('required', $errors[0]->code);
    }

    public function testRejectsUnknownComponentType(): void
    {
        $errors = $this->validator->validate([
            'root' => [
                'type' => 'container',
                'children' => [
                    [
                        'type' => 'hero_banner',
                        'props' => [],
                    ],
                ],
            ],
        ], 'twindigital');

        self::assertNotEmpty($errors);
        self::assertSame('/root/children/0/type', $errors[0]->path);
        self::assertSame('unsupported_component', $errors[0]->code);
    }

    public function testAcceptsNestedContainerAndBlockReferenceTree(): void
    {
        $errors = $this->validator->validate([
            'root' => [
                'type' => 'container',
                'children' => [
                    [
                        'type' => 'container',
                        'children' => [
                            [
                                'type' => 'block_ref',
                                'props' => [
                                    'blockId' => 'block-a',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], 'twindigital');

        self::assertSame([], $errors);
    }
}
