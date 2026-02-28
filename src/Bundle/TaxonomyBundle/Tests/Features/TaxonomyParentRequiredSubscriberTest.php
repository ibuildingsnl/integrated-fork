<?php

declare(strict_types=1);

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\TaxonomyBundle\EventListener\TaxonomyParentRequiredSubscriber;
use Integrated\Common\Content\Form\Event\FieldEvent;
use Integrated\Common\Form\Mapping\Metadata\Field;
use Integrated\Common\Form\Mapping\MetadataInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;

final class TaxonomyParentRequiredSubscriberTest extends TestCase
{
    public function testMarksParentFieldAsRequiredWhenOptionIsEnabled(): void
    {
        $subscriber = new TaxonomyParentRequiredSubscriber();
        $field = (new Field('parent_id'))->setOptions([
            'allow_clear' => true,
        ]);

        $event = new FieldEvent($this->taxonomyContentType(true), $this->metadata(), $field, []);

        $subscriber->buildField($event);

        $options = $field->getOptions();

        self::assertTrue($options['required']);
        self::assertFalse($options['allow_clear']);
        self::assertTrue($this->containsNotBlankConstraint($options['constraints'] ?? []));
    }

    public function testLeavesParentFieldUntouchedWhenOptionIsDisabled(): void
    {
        $subscriber = new TaxonomyParentRequiredSubscriber();
        $field = (new Field('parent_id'))->setOptions([
            'allow_clear' => true,
            'required' => false,
        ]);
        $original = $field->getOptions();

        $event = new FieldEvent($this->taxonomyContentType(false), $this->metadata(), $field, []);

        $subscriber->buildField($event);

        self::assertSame($original, $field->getOptions());
    }

    public function testIgnoresOtherFields(): void
    {
        $subscriber = new TaxonomyParentRequiredSubscriber();
        $field = (new Field('title'))->setOptions([
            'required' => false,
        ]);
        $original = $field->getOptions();

        $event = new FieldEvent($this->taxonomyContentType(true), $this->metadata(), $field, []);

        $subscriber->buildField($event);

        self::assertSame($original, $field->getOptions());
    }

    private function taxonomyContentType(bool $enforceParent): ContentType
    {
        return (new ContentType())
            ->setClass(Taxonomy::class)
            ->setOption('enforce_parent', $enforceParent);
    }

    private function metadata(): MetadataInterface
    {
        return $this->createMock(MetadataInterface::class);
    }

    /**
     * @param array<int, mixed> $constraints
     */
    private function containsNotBlankConstraint(array $constraints): bool
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof NotBlank) {
                return true;
            }
        }

        return false;
    }
}
