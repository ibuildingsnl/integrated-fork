<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Document\Block;

use Integrated\Bundle\ContentBundle\Document\Block\FacetBlock;
use Integrated\Common\Form\Mapping\Attributes\Field;
use PHPUnit\Framework\TestCase;

final class FacetBlockTest extends TestCase
{
    public function testFacetBlockDefaultsToOrOperator(): void
    {
        $block = new FacetBlock();

        self::assertSame('or', $block->getOperator());
    }

    public function testFacetBlockNormalizesUnknownOperatorBackToOr(): void
    {
        $block = new FacetBlock();
        $block->setOperator('unexpected');

        self::assertSame('or', $block->getOperator());
    }

    public function testFacetBlockDefaultsToSingleSelectionMode(): void
    {
        $block = new FacetBlock();

        self::assertSame('single', $block->getSelectionMode());
    }

    public function testFacetBlockNormalizesUnknownSelectionModeBackToSingle(): void
    {
        $block = new FacetBlock();
        $block->setSelectionMode('unexpected');

        self::assertSame('single', $block->getSelectionMode());
    }

    public function testFacetBlockOperatorFieldDoesNotRenderBlankPlaceholderOption(): void
    {
        $reflection = new \ReflectionProperty(FacetBlock::class, 'operator');
        $attributes = $reflection->getAttributes(Field::class);

        self::assertCount(1, $attributes);

        /** @var Field $field */
        $field = $attributes[0]->newInstance();

        self::assertFalse($field->getOptions()['placeholder'] ?? null);
    }
}
