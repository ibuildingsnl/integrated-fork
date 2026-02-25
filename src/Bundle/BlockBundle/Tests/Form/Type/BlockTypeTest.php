<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Form\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Form\Type\BlockType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockTypeTest extends TestCase
{
    public function testConfigureOptionsProvidesSearchableChoices(): void
    {
        $first = new TestBlock();
        $first->setId('alpha-id');
        $first->setTitle('Alpha');

        $second = new TestBlock();
        $second->setId('beta-id');
        $second->setTitle(' ');

        $type = $this->createTypeWithBlocks([$first, $second]);

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        self::assertSame(ChoiceType::class, $type->getParent());
        self::assertSame([$first, $second], $options['choices']);
        self::assertSame('select2', $options['attr']['class']);

        $choiceLabel = $options['choice_label'];
        $choiceValue = $options['choice_value'];

        self::assertSame('Alpha (alpha-id)', $choiceLabel($first));
        self::assertSame('beta-id', $choiceLabel($second));
        self::assertSame('alpha-id', $choiceValue($first));
        self::assertNull($choiceValue(null));
    }

    public function testConfigureOptionsAcceptsTraversableResults(): void
    {
        $block = new TestBlock();
        $block->setId('id-1');
        $block->setTitle('Block 1');

        $type = $this->createTypeWithBlocks(new \ArrayIterator([$block]));

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        self::assertSame([$block], $options['choices']);
    }

    /**
     * @param array<int, Block>|\Traversable<int, Block> $blocks
     */
    private function createTypeWithBlocks(array|\Traversable $blocks): BlockType
    {
        $repository = $this->createMock(DocumentRepository::class);

        $dm = $this->createMock(DocumentManager::class);
        $dm->expects(self::once())
            ->method('getRepository')
            ->with(Block::class)
            ->willReturn($repository);

        return new TestableBlockType($dm, $blocks);
    }
}

class TestBlock extends Block
{
    public function getType()
    {
        return 'test';
    }
}

class TestableBlockType extends BlockType
{
    /**
     * @param array<int, Block>|\Traversable<int, Block> $choices
     */
    public function __construct(DocumentManager $dm, private array|\Traversable $choices)
    {
        parent::__construct($dm);
    }

    protected function fetchChoices(): iterable
    {
        return $this->choices;
    }
}
