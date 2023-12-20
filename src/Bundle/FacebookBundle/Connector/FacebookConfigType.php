<?php

namespace Integrated\Bundle\FacebookBundle\Connector;

use Integrated\Bundle\FacebookBundle\Form\PopulateFacebookPageFieldListener;
use Integrated\Bundle\FormTypeBundle\Form\Type\RefreshableChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FacebookConfigType extends AbstractType
{
    public function __construct(
        private readonly FacebookClient $client,
        private readonly RequestStack   $stack,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new PopulateFacebookPageFieldListener($this->client, $this->stack));
        $builder->add('token_secret', TextType::class, ['attr' => ['readonly' => 'true']]);
        $builder->add('page', ChoiceType::class, ['choices' => ['Please finish setting up connection to Facebook' => 'empty'], 'attr' => ['disabled' => 'true']]);
        $builder->add('page_token', TextType::class, ['attr' => ['readonly' => 'true']]);
        $builder->add('api_status', TextType::class, ['attr' => ['disabled' => 'true']]);
        $builder->add('test', RefreshableChoiceType::class, [
            'select' => [
                'choices' => [
                    'Test' => 'test',
                    'Test2' => 'test2',
                    'Test3' => 'test3'
                ]
            ],
            'submit' => [
                'attr' => [
                    'style' => 'height: unset;'
                ],
                'label' => false,
                'icon' => 'iconoir-refresh-double',
            ]
        ]);
    }

    public function getBlockPrefix()
    {
        return 'integrated_social_facebook';
    }
}
