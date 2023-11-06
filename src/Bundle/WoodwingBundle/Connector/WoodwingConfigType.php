<?php

namespace Integrated\Bundle\WoodwingBundle\Connector;

//use Integrated\Bundle\LinkedInBundle\Form\AddWoodwingPageFieldListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class WoodwingConfigType extends AbstractType
{
    private $woodwing;

    public function __construct(WoodwingFactory $woodwing)
    {
        $this->woodwing = $woodwing;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        dd($this->linkedin);
        $builder->addEventSubscriber(new AddWoodwingPageFieldListener($this->linkedin));
        $builder->add('token', TextType::class, ['attr' => ['readonly' => 'true']]);
        $builder->add('page', TextType::class, ['attr' => ['readonly' => 'false']]);
        $builder->add('apiStatus', TextType::class, ['attr' => ['readonly' => 'false']]);
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_social_linkedin';
    }
}
