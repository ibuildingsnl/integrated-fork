<?php

namespace Integrated\Bundle\FormTypeBundle\Tests\Form\Type\RelationChoice\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\FormTypeBundle\Form\Type\RelationChoice\EventListener\AddRelationFieldsSubscriber;
use Integrated\Common\ContentType\ContentTypeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class AddRelationFieldsSubscriberTest extends TestCase
{
    public function testPreSetDataBatchLoadsConfiguredRelations(): void
    {
        $relationA = $this->createRelation('relation_a', 'Target A');
        $relationB = $this->createRelation('relation_b', 'Target B');

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->expects(self::once())
            ->method('findBy')
            ->with(['$or' => [['id' => 'relation_a'], ['id' => 'relation_b']]])
            ->willReturn([$relationA, $relationB]);
        $repository
            ->expects(self::never())
            ->method('find');

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->method('getRepository')
            ->with(Relation::class)
            ->willReturn($repository);

        $parentForm = $this->createMock(FormInterface::class);
        $parentForm->method('getData')->willReturn(new Article());

        $form = $this->createMock(FormInterface::class);
        $form->method('getParent')->willReturn($parentForm);
        $form
            ->expects(self::exactly(2))
            ->method('add')
            ->with(
                self::anything(),
                'Integrated\Bundle\FormTypeBundle\Form\Type\RelationChoice\RelationReferencesType',
                self::anything()
            )
            ->willReturnSelf();

        $relations = new ArrayCollection();
        $subscriber = new AddRelationFieldsSubscriber($documentManager, [
            'relations' => ['relation_a', 'relation_b'],
            'options' => [],
        ]);

        $subscriber->preSetData(new FormEvent($form, $relations));

        self::assertCount(2, $relations);
    }

    private function createRelation(string $id, string $name): Relation
    {
        $source = $this->createMock(ContentTypeInterface::class);
        $source->method('getClass')->willReturn(Article::class);

        $target = $this->createMock(ContentTypeInterface::class);
        $target->method('getId')->willReturn($id.'_target');

        $relation = (new Relation())
            ->setId($id)
            ->setName($name)
            ->setType('single');
        $relation->addSource($source);
        $relation->addTarget($target);

        return $relation;
    }
}
