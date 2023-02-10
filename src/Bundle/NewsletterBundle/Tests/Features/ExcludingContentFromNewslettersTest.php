<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Event;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\JobPosting;
use Integrated\Bundle\ContentBundle\Document\Content\News;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Company;
use Integrated\Bundle\ContentBundle\Document\Content\Video;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Form\Type\CustomFieldsType;
use Integrated\Bundle\NewsletterBundle\EventListener\ExcludeFromNewsletterListener;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Form\Mapping\Metadata\Document;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;

final class ExcludingContentFromNewslettersTest extends TestCase
{
    private FormBuilderInterface $builder;
    private ExcludeFromNewsletterListener $listener;
    private BuilderEvent $event;

    protected function setUp(): void
    {
        $this->builder = new FormBuilder(
            'form',
            Content::class,
            new EventDispatcher(),
            new FormFactory(new FormRegistry([], new ResolvedFormTypeFactory()))
        );
        $this->listener = new ExcludeFromNewsletterListener(
            Article::class,
            Event::class,
            JobPosting::class,
            News::class,
            Video::class,
            Company::class,
        );
        $type = new ContentType();
        $type->setClass(Article::class);
        $this->event = new BuilderEvent($type, new Document(null), $this->builder, []);
    }

    public function testAddingAnExcludeFieldToCustomFields()
    {
        $this->builder->add('customFields', CustomFieldsType::class, [
            'contentType' => new ContentType(),
            'attr' => [
                'style' => 'editor',
            ],
        ]);

        $this->listener->onPostBuild($this->event);

        self::assertTrue($this->builder->get('customFields')->has('ExcludeFromNewsletters'));
    }

    public function testAddingCustomFieldsTypeIfNotYetPresent()
    {
        $this->listener->onPostBuild($this->event);

        self::assertTrue($this->builder->has('customFields'));
        self::assertTrue($this->builder->get('customFields')->has('ExcludeFromNewsletters'));
    }

    public function testNotAddingAnExcludeFieldForDocumentsThatCannotBeAddedAnyway()
    {
        $this->builder->add('customFields', CustomFieldsType::class, [
            'contentType' => new ContentType(),
            'attr' => [
                'style' => 'editor',
            ],
        ]);
        $type = new ContentType();
        $type->setClass(Image::class);

        $this->listener->onPostBuild(new BuilderEvent($type, new Document(null), $this->builder, []));

        self::assertFalse($this->builder->get('customFields')->has('ExcludeFromNewsletters'));
    }

    public function testNotAddingEmptyCustomFieldsForDocumentsThatCannotBeAddedAnyway()
    {
        $type = new ContentType();
        $type->setClass(Image::class);

        $this->listener->onPostBuild(new BuilderEvent($type, new Document(null), $this->builder, []));

        self::assertFalse($this->builder->has('customFields'));
    }
}
