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

use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOptions;
use Integrated\Bundle\UserBundle\Form\Type\GroupType;
use Integrated\Bundle\UserBundle\Model\Group;
use Integrated\Bundle\UserBundle\Model\GroupManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class SearchSelectionType extends AbstractType
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly GroupManagerInterface $groups,
        private readonly SortOptions $sortingOptions,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class);
        $builder->add('sort', ChoiceType::class, [
            'label' => 'Sort by',
            'mapped' => false,
            'required' => false,
            'placeholder' => 'Default sorting',
            'choices' => $this->getSortChoices(),
            'help' => 'Select a standard sorting field for this search selection. Choose "Custom" if you want to sort by a specific Solr field name.',
        ]);
        $builder->add('order', ChoiceType::class, [
            'label' => 'Sort order',
            'mapped' => false,
            'required' => false,
            'placeholder' => 'Default order',
            'choices' => [
                'Ascending' => 'asc',
                'Descending' => 'desc',
            ],
        ]);
        $builder->add('customSort', TextType::class, [
            'label' => 'Custom sorting',
            'mapped' => false,
            'required' => false,
            'attr' => [
                'placeholder' => 'publication_start_vismagazine_index_date',
            ],
            'row_attr' => [
                'class' => 'search-selection-custom-sort-row',
            ],
            'help' => 'How custom sorting works: 1) Select "Custom" in "Sort by". 2) Enter a Solr sort expression without the "custom:" prefix, for example publication_start_vismagazine_index_date or profile_type_sort_text desc, title_sort asc. 3) Use "Sort order" only for a single custom field without an explicit direction.',
        ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $data = $event->getData();
            if (!$data instanceof SearchSelection) {
                return;
            }

            $filters = $data->getFilters();
            $sort = isset($filters['sort']) && \is_string($filters['sort']) ? trim($filters['sort']) : '';
            $order = isset($filters['order']) && \is_string($filters['order']) ? strtolower(trim($filters['order'])) : '';

            $form = $event->getForm();

            if (str_starts_with($sort, 'custom:')) {
                $form->get('sort')->setData('__custom__');
                $form->get('customSort')->setData(substr($sort, 7));
            } else {
                $form->get('sort')->setData($sort);
            }

            if (\in_array($order, ['asc', 'desc'], true)) {
                $form->get('order')->setData($order);
            }
        });

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $builder->add('public', ChoiceType::class, [
                'label' => 'Available for',
                'expanded' => true,
                'choices' => [
                    'Everyone' => 1,
                    'Myself only' => 0,
                ],
            ]);
            $builder->add('groupId', GroupType::class, [
                'placeholder' => 'Select a group',
                'required' => false,
            ]);
            $builder->get('groupId')->addModelTransformer(new CallbackTransformer(
                fn (?string $id) => $id ? $this->groups->find($id) : null,
                fn (?Group $group) => $group?->getId(),
            ));
        }

        $builder->add('inMenu', CheckboxSwitcherType::class, [
            'required' => false,
            'label' => 'Add to menu',
            'attr' => [
                'align_with_widget' => true,
            ],
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function getSortChoices(): array
    {
        $choices = [];

        foreach ($this->sortingOptions->all() as $option) {
            $choices[ucfirst($option->label)] = $option->field;
        }

        $choices['Custom'] = '__custom__';

        return $choices;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_search_selection';
    }
}
