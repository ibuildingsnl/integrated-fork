<?php

namespace Integrated\Bundle\WoodwingBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\ContentRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Form\Type\PublishTimeType;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WoodwingPublicationType extends AbstractType
{
    public function __construct(
        private readonly ContentRepository $content,
        private readonly DocumentManager $dm,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formChannel = $this->dm->createQueryBuilder(Channel::class)
                                ->field('name')
                                ->equals($options['label'])
                                ->getQuery()
                                ->getSingleResult();

        $builder->add('time', PublishTimeType::class, ['label' => false]);

        $withChannels = ChoiceList::attr($this, fn(?Taxonomy $taxonomy) => [
            'data-channels' => implode(';', array_map(
                fn(ChannelInterface $channel) => $channel->getId(),
                $taxonomy?->getChannels() ?: []
            )),
        ]);
        $builder->add('edition', DocumentType::class, [
            'class' => Taxonomy::class,
            'query_builder' => fn(DocumentRepository $repository) => $repository->createQueryBuilder()
                ->field('contentType')->equals($options['editionType'])
                ->field('channels.$id')->equals($formChannel->getId())
                ->field('parent_id')->notEqual(null),
            'choice_label' => 'title',
            'choice_value' => function (?Taxonomy $entity): string {
                return $entity ? $entity->getId() : '';
            },
            'choice_attr' => $withChannels,
            'multiple' => false,
        ]);

        $builder->add('layout', DocumentType::class, [
            'class' => Taxonomy::class,
            'query_builder' => fn(DocumentRepository $repository) => $repository->createQueryBuilder()
                ->field('contentType')->equals($options['layoutType'])
                ->field('channels.$id')->equals($formChannel->getId())
                ->field('parent_id')->notEqual(null),
            'choice_label' => 'title',
            'choice_value' => function (?Taxonomy $entity): string {
                return $entity ? $entity->getId() : '';
            },
            'choice_attr' => $withChannels,
            'multiple' => false,
        ]);
        $builder->add('send', CheckboxType::class, [
            'label' => 'Send to Woodwing',
        ]);

        $transformer = new CallbackTransformer(
            fn (?string $id) => $this->content->find($id),
            fn (?Content $item) => $item?->getId(),
        );
        $builder->get('edition')->addModelTransformer($transformer);

        $builder->get('layout')->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'editionType' => 'edition',
            'layoutType' => 'layout',
        ]);
    }
}
