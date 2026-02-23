<?php

namespace Integrated\Bundle\UserBundle\Form\Type;

use Integrated\Bundle\UserBundle\Provider\FilterQueryProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFilterType extends AbstractType
{
    /**
     * @var FilterQueryProvider
     */
    private $filterQueryProvider;

    public function __construct(FilterQueryProvider $filterQueryProvider)
    {
        $this->filterQueryProvider = $filterQueryProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod(\Symfony\Component\HttpFoundation\Request::METHOD_GET);

        $builder
            ->add('q', TextType::class, [
                'label' => 'Find by username',
                'required' => false,
            ])
            ->add('groups', ChoiceType::class, [
                'choices' => $this->filterQueryProvider->getGroupChoices($options['data']),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('scope', ChoiceType::class, [
                'choices' => $this->filterQueryProvider->getScopeChoices($options['data']),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('has_relation', ChoiceType::class, [
                'label' => 'Author linked',
                'choices' => [
                    'Linked to author' => '1',
                    'Not linked' => '0',
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('data');
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_user_filter';
    }
}
