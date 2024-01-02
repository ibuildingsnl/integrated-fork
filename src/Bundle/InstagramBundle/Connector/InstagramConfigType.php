<?php

namespace Integrated\Bundle\InstagramBundle\Connector;

use Integrated\Bundle\FormTypeBundle\Form\Type\RefreshableChoiceType;
use Integrated\Bundle\InstagramBundle\Form\PopulateInstagramPageFieldListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class InstagramConfigType extends AbstractType
{
    public function __construct(
        private readonly InstagramClient $client,
        private readonly RequestStack   $stack,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new PopulateInstagramPageFieldListener($this->client, $this->stack));
        $builder->add('token_secret', TextType::class, ['attr' => ['readonly' => 'true']]);
        $builder->add('page', RefreshableChoiceType::class, ['select' => ['choices' => ['Please finish setting up connection to Instagram' => 'empty'], 'attr' => ['disabled' => 'true']]]);
        $builder->add('page_token', TextType::class, ['attr' => ['readonly' => 'true']]);
        $builder->add('api_status', TextType::class, ['attr' => ['disabled' => 'true']]);
    }

    public function getBlockPrefix()
    {
        return 'integrated_social_instagram';
    }
}
