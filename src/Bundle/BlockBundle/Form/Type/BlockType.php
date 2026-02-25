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
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class BlockType extends AbstractType
{
    /**
     * @var DocumentRepository
     */
    protected $repository;

    public function __construct(DocumentManager $dm)
    {
        $this->repository = $dm->getRepository(Block::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->getChoices(),
            'choice_label' => static function (?Block $block): string {
                if (!$block instanceof Block) {
                    return '';
                }

                $title = trim((string) $block->getTitle());
                $id = (string) $block->getId();

                return '' !== $title ? sprintf('%s (%s)', $title, $id) : $id;
            },
            'choice_value' => static fn (?Block $block): ?string => $block?->getId(),
            'required' => false,
            'attr' => [
                'class' => 'select2',
            ],
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_block';
    }

    /**
     * @return array<int, Block>
     */
    private function getChoices(): array
    {
        $result = $this->fetchChoices();

        if ($result instanceof \Traversable) {
            $result = iterator_to_array($result, false);
        }

        return array_values(array_filter($result, static fn ($block): bool => $block instanceof Block));
    }

    /**
     * @return iterable<int, mixed>
     */
    protected function fetchChoices(): iterable
    {
        return $this->repository->createQueryBuilder()
            ->sort('title', 'asc')
            ->getQuery()
            ->execute();
    }
}
