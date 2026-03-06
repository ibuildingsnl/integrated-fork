<?php

declare(strict_types=1);

namespace Integrated\Bundle\TaxonomyBundle\EventListener;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Common\Content\Form\Event\FieldEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

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
        $constraints = $this->normalizeConstraints($options['constraints'] ?? []);
        if (!$this->containsNotBlankConstraint($constraints)) {
            $constraints[] = new NotBlank();
        }

        $options['required'] = true;
        $options['allow_clear'] = false;
        $options['constraints'] = $constraints;

        $field->setOptions($options);
    }

    /**
     * @param mixed $constraints
     *
     * @return array<int, mixed>
     */
    private function normalizeConstraints($constraints): array
    {
        if (\is_array($constraints)) {
            return $constraints;
        }

        if (null === $constraints) {
            return [];
        }

        return [$constraints];
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
