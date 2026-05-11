<?php

declare(strict_types=1);

namespace Integrated\Bundle\TaxonomyBundle\EventListener;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Common\Content\Form\Event\FieldEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class TaxonomyParentRequiredSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::BUILD_FIELD => 'buildField',
        ];
    }

    public function buildField(FieldEvent $event): void
    {
        $field = $event->getField();

        if ('parent_id' !== $field->getName()) {
            return;
        }

        if (!is_a((string) $event->getContentType()->getClass(), Taxonomy::class, true)) {
            return;
        }

        if (!(bool) $event->getContentType()->getOption('enforce_parent')) {
            return;
        }

        $options = $field->getOptions();
        $constraints = $this->removeNotBlankConstraints($this->normalizeConstraints($options['constraints'] ?? []));
        if (!$this->containsConditionalParentConstraint($constraints)) {
            $constraints[] = new Callback([self::class, 'validateParentWhenChannelNotLinked']);
        }

        $options['required'] = false;
        $options['allow_clear'] = true;
        $options['constraints'] = $constraints;

        $field->setOptions($options);
    }

    public static function validateParentWhenChannelNotLinked(mixed $parentId, ExecutionContextInterface $context): void
    {
        if ('' !== trim((string) $parentId)) {
            return;
        }

        $taxonomy = self::resolveTaxonomy($context);
        if ($taxonomy instanceof Taxonomy && '' !== trim((string) $taxonomy->getLinkToChannel())) {
            return;
        }

        $context->buildViolation('This value should not be blank.')->addViolation();
    }

    /**
     * @return array<int, mixed>
     */
    private function normalizeConstraints(mixed $constraints): array
    {
        if (\is_array($constraints)) {
            return $constraints;
        }

        if (null === $constraints) {
            return [];
        }

        return [$constraints];
    }

    private static function resolveTaxonomy(ExecutionContextInterface $context): ?Taxonomy
    {
        $root = $context->getRoot();
        if ($root instanceof FormInterface && $root->getData() instanceof Taxonomy) {
            return $root->getData();
        }

        if ($root instanceof Taxonomy) {
            return $root;
        }

        $object = $context->getObject();
        if ($object instanceof Taxonomy) {
            return $object;
        }

        return null;
    }

    /**
     * @param array<int, mixed> $constraints
     *
     * @return array<int, mixed>
     */
    private function removeNotBlankConstraints(array $constraints): array
    {
        return array_values(array_filter(
            $constraints,
            static fn (mixed $constraint): bool => !$constraint instanceof NotBlank,
        ));
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

            if ([self::class, 'validateParentWhenChannelNotLinked'] === $constraint->callback) {
                return true;
            }
        }

        return false;
    }
}
