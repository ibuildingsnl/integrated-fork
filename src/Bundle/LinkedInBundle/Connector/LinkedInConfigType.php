<?php

namespace Integrated\Bundle\LinkedInBundle\Connector;

use Integrated\Bundle\LinkedInBundle\Form\AddLinkedInPageFieldListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;


class LinkedInConfigType extends AbstractType
{
    private $linkedin;

    public function __construct(LinkedInFactory $linkedin)
    {
        $this->linkedin = $linkedin;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        dd($this->linkedin);
        $builder->addEventSubscriber(new AddLinkedInPageFieldListener($this->linkedin));
        $builder->add('token', TextType::class, ['attr' => ['readonly' => 'true']]);
        $builder->add('page', TextType::class, ['attr' => ['readonly' => 'false']]);
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_social_linkedin';
    }
}
