<?php

namespace Integrated\Bundle\BrandBundle\Form\Type;

use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\Document\LinkType;
use Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ChannelLinkType extends AbstractType
{
    /** @param LinkType[] $linkTypes */
    public function __construct(
        private readonly ChannelRepository $channels,
        private readonly iterable $linkTypes,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', ChoiceType::class, [
            'choices' => $this->linkTypes,
            'choice_label' => 'name',
            'choice_value' => 'id',
        ]);
        $builder->add('channel', ChannelChoiceType::class, ['multiple' => false]);
        $builder->add('default', CheckboxType::class, ['required' => false]);

        $builder->addModelTransformer(new CallbackTransformer(
            fn (?ChannelLink $link) => $link ? [
                'type' => $link->type,
                'channel' => $link->channel->getId(),
                'default' => $link->default,
            ] : null,
            fn (?array $data) => $data ? new ChannelLink(
                $data['type'],
                $this->channels->find($data['channel']),
                $data['default']
            ): null,
        ));
    }
}
