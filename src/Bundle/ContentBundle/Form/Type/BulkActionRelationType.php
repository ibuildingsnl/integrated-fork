<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Bulk\RelationAddHandler;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\RelationAction;
use Integrated\Common\Content\Relation\RelationInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class BulkActionRelationType extends AbstractType
{
    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @var string
     */
    private $route;

    /**
     * @var array
     */
    private $routeParams;

    public function __construct(UrlGeneratorInterface $generator, string $route, array $routeParams = [])
    {
        $this->generator = $generator;
        $this->route = $route;
        $this->routeParams = $routeParams;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $referenceOptions = [
            'label' => $options['label'],
            'attr' => [
                'data-id' => $options['relation']->getId(),
                'data-url' => $this->generator->generate($this->route, $this->routeParams),
                'class' => 'relation-items',
            ],
            'relation_id' => $options['relation']->getId(),
            'relation_title' => $options['relation']->getName(),
            'relation_type' => $options['relation']->getType(),
            'taxonomy_categories' => $options['taxonomy_categories'],
        ];

        if ($options['relation']->getType() === 'taxonomy_category') {
            $referenceOptions['choices'] = $this->buildTaxonomyChoices($options['taxonomy_categories']);
        }

        $builder->add(
            'references',
            BulkActionRelationReferencesType::class,
            $referenceOptions
        );

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($referenceOptions): void {
            $data = $event->getData();
            if (!\is_array($data) || !isset($data['references'])) {
                return;
            }

            $dynamicChoices = $this->buildSubmittedChoices($data['references']);
            if (!$dynamicChoices) {
                return;
            }

            $dynamicReferenceOptions = $referenceOptions;
            $dynamicReferenceOptions['choices'] = ($dynamicReferenceOptions['choices'] ?? []) + $dynamicChoices;

            $event->getForm()->add('references', BulkActionRelationReferencesType::class, $dynamicReferenceOptions);
        });

        $relationType = (string) $options['relation']->getType();
        $relationId = (string) $options['relation']->getId();
        $supportsReplaceExisting = \in_array($relationType, ['taxonomy_category', 'taxonomy_tags'], true)
            || $relationId === 'tag';

        if (
            $supportsReplaceExisting
            && $options['relation_handler'] === RelationAddHandler::class
        ) {
            $builder->add(
                'replaceExisting',
                CheckboxSwitcherType::class,
                [
                    'required' => false,
                    'label' => 'Clear existing taxonomy selection before applying',
                ]
            );
        }
    }

    /**
     * @param array<mixed> $taxonomyCategories
     *
     * @return array<string, string>
     */
    private function buildTaxonomyChoices(array $taxonomyCategories): array
    {
        $choices = [];

        foreach ($taxonomyCategories as $taxonomyCategory) {
            $taxonomyId = $this->resolveTaxonomyId($taxonomyCategory);

            if (null === $taxonomyId || '' === $taxonomyId) {
                continue;
            }

            $choices[(string) $taxonomyId] = (string) $taxonomyId;
        }

        return $choices;
    }

    /**
     * @param mixed $submittedReferences
     *
     * @return array<string, string>
     */
    private function buildSubmittedChoices(mixed $submittedReferences): array
    {
        if (!\is_array($submittedReferences)) {
            $submittedReferences = [$submittedReferences];
        }

        $choices = [];

        foreach ($submittedReferences as $submittedReference) {
            if (!\is_scalar($submittedReference) || '' === (string) $submittedReference) {
                continue;
            }

            $referenceId = (string) $submittedReference;
            $choices[$referenceId] = $referenceId;
        }

        return $choices;
    }

    private function resolveTaxonomyId(mixed $taxonomyCategory): ?string
    {
        if (\is_array($taxonomyCategory) && isset($taxonomyCategory['taxonomyId'])) {
            return (string) $taxonomyCategory['taxonomyId'];
        }

        if (\is_object($taxonomyCategory)) {
            if (method_exists($taxonomyCategory, 'getTaxonomyId')) {
                return (string) $taxonomyCategory->getTaxonomyId();
            }

            if (isset($taxonomyCategory->taxonomyId)) {
                return (string) $taxonomyCategory->taxonomyId;
            }
        }

        return null;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['relation', 'relation_handler', 'taxonomy_categories'])
            ->setAllowedTypes('relation', RelationInterface::class)
            ->setAllowedTypes('relation_handler', 'string')
            ->setAllowedTypes('taxonomy_categories', 'array')
            ->setDefault('data_class', RelationAction::class)
            ->setDefault('empty_data', function (Options $options) {
                $action = new RelationAction();

                $action->setRelation($options['relation']);
                $action->setHandler($options['relation_handler']);

                return $action;
            });
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_content_bulk_action_relation';
    }
}
