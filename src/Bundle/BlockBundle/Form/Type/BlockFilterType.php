<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Form\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BlockBundle\Document\Block\BlockRepository;
use Integrated\Bundle\BlockBundle\Provider\BlockUsageProvider;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Vasil Pascal <developer.optimum@gmail.com>
 */
class BlockFilterType extends AbstractType
{
    /**
     * @var MetadataFactoryInterface
     */
    private $factory;

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var BlockUsageProvider
     */
    private $blockUsageProvider;

    private BlockRepository $blockRepository;

    public function __construct(
        MetadataFactoryInterface $factory,
        DocumentManager $dm,
        BlockUsageProvider $blockUsageProvider,
        BlockRepository $blockRepository,
    ) {
        $this->factory = $factory;
        $this->dm = $dm;
        $this->blockUsageProvider = $blockUsageProvider;
        $this->blockRepository = $blockRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod(\Symfony\Component\HttpFoundation\Request::METHOD_GET);

        $builder->add(
            'q',
            TextType::class,
            [
                'required' => false,
                'label' => 'Filter by block name',
            ]
        );

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'choices' => $this->getTypeChoices($options['blockIds']),
                'expanded' => true,
                'multiple' => true,
            ]
        );

        $builder->add(
            'channels',
            ChoiceType::class,
            [
                'choices' => $this->getChannelChoices($options['blockIds']),
                'expanded' => true,
                'multiple' => true,
            ]
        );

        $builder->add(
            'unused',
            CheckboxType::class,
            [
                'required' => false,
                'label' => 'Unused',
            ]
        );

        $builder->add('submit', SubmitType::class, [
            'label' => 'Filter',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('blockIds');
        $resolver->setAllowedTypes('blockIds', 'array');
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_block_filter';
    }

    private function getTypeChoices(array $blockIds)
    {
        return $this->blockRepository->getTypeChoices(
            $this->factory,
            $blockIds
        );
    }

    /**
     * @return array
     */
    private function getChannelChoices(array $blockIds)
    {
        $channels = $this->blockUsageProvider->getBlocksPerChannel();

        $channelChoices = [];
        foreach ($channels as $channelId => $blocks) {
            $count = \count(array_intersect((array) $blocks, $blockIds));
            if ($count) {
                if ($channel = $this->blockUsageProvider->getChannel($channelId)) {
                    $channelChoices[$channel->getName().' ('.$count.')'] = $channelId;
                }
            }
        }

        ksort($channelChoices);

        return $channelChoices;
    }
}
