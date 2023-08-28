<?php

namespace Integrated\Bundle\BrandBundle\Form\Type;

use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BrandChoiceType extends AbstractType
{
    public function __construct(
        private readonly BrandRepository $brands,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->brands->all() as $brand) {
            $links = [];
            foreach ($brand->getChannelLinks() as $link) {
                if (in_array($link->channel, $options['channel_choices'])) {
                    $links[] = $link;
                }
            }
            if (!empty($links)) {
                $builder->add($brand->profile->getId(), BrandChannelChoiceType::class, [
                    'links' => $links,
                    'brand_name' => $brand->getName(),
                    'label' => false,
                ]);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('channel_choices', []);
    }
}
