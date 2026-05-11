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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class TaxonomyParentRequiredSubscriberTest extends TestCase
{
    public function testAddsConditionalParentConstraintWhenOptionIsEnabled(): void
    {
        $subscriber = new TaxonomyParentRequiredSubscriber();
        $field = (new Field('parent_id'))->setOptions([
            'allow_clear' => true,
        ]);

        $event = new FieldEvent($this->taxonomyContentType(true), $this->metadata(), $field, []);

        $subscriber->buildField($event);

        $options = $field->getOptions();

        self::assertFalse($options['required']);
        self::assertTrue($options['allow_clear']);
        self::assertFalse($this->containsNotBlankConstraint($options['constraints'] ?? []));
        self::assertTrue($this->containsConditionalParentConstraint($options['constraints'] ?? []));
    }

    public function testConditionalParentConstraintAllowsMissingParentWhenLinkToChannelIsFilled(): void
    {
        $taxonomy = new Taxonomy();
        $taxonomy->setLinkToChannel('website');

        $form = $this->createMock(FormInterface::class);
        $form->method('getData')->willReturn($taxonomy);

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->method('getRoot')->willReturn($form);
        $context->expects(self::never())->method('buildViolation');

        TaxonomyParentRequiredSubscriber::validateParentWhenChannelNotLinked('', $context);
    }

    public function testConditionalParentConstraintRequiresParentWhenLinkToChannelIsEmpty(): void
    {
        $taxonomy = new Taxonomy();
        $taxonomy->setLinkToChannel(null);

        $form = $this->createMock(FormInterface::class);
        $form->method('getData')->willReturn($taxonomy);

        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $builder->expects(self::once())->method('addViolation');

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->method('getRoot')->willReturn($form);
        $context
            ->expects(self::once())
            ->method('buildViolation')
            ->with('This value should not be blank.')
            ->willReturn($builder);

        TaxonomyParentRequiredSubscriber::validateParentWhenChannelNotLinked('', $context);
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

    /**
     * @param array<int, mixed> $constraints
     */
    private function containsConditionalParentConstraint(array $constraints): bool
    {
        foreach ($constraints as $constraint) {
            if (!$constraint instanceof Callback) {
                continue;
            }

            if ([TaxonomyParentRequiredSubscriber::class, 'validateParentWhenChannelNotLinked'] === $constraint->callback) {
                return true;
            }
        }

        return false;
    }
}
