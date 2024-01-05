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
        $builder->add('page', RefreshableChoiceType::class, ['select' => ['choices' => ['Please finish setting up connection to Facebook' => 'empty'], 'attr' => ['disabled' => 'true']]]);
        $builder->add('page_token', TextType::class, ['attr' => ['readonly' => 'true']]);
        $builder->add('api_status', TextType::class, ['attr' => ['disabled' => 'true']]);
    }

    public function getBlockPrefix()
    {
        return 'integrated_social_facebook';
    }
}
