<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Form\EventListener;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Form\EventListener\FeaturedExpirationSelectionListener;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Form\Mapping\Metadata\Document;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;

final class FeaturedExpirationSelectionListenerTest extends TestCase
{
    private FormBuilderInterface $builder;
    private FeaturedExpirationSelectionListener $listener;

    protected function setUp(): void
    {
        $this->builder = new FormBuilder(
            'form',
            Content::class,
            new EventDispatcher(),
            new FormFactory(new FormRegistry([], new ResolvedFormTypeFactory()))
        );
        $this->builder->add('featured', CheckboxType::class);
        $this->listener = new FeaturedExpirationSelectionListener();
    }

    public function testAddsFeaturedExpirationFieldWhenContentTypeOptionIsEnabled(): void
    {
        $type = new ContentType();
        $type->setClass(Article::class);
        $type->setOption('featured_expiration', true);

        $event = new BuilderEvent($type, new Document(null), $this->builder, []);

        $this->listener->onPostBuild($event);

        self::assertTrue($this->builder->has('featured_expiration'));
        self::assertInstanceOf(
            IntegerType::class,
            $this->builder->get('featured_expiration')->getType()->getInnerType()
        );
        self::assertSame('featuredExpiration', $this->builder->get('featured_expiration')->getOption('property_path'));
    }

    public function testDoesNotAddFeaturedExpirationFieldWhenOptionIsDisabled(): void
    {
        $type = new ContentType();
        $type->setClass(Article::class);

        $event = new BuilderEvent($type, new Document(null), $this->builder, []);

        $this->listener->onPostBuild($event);

        self::assertFalse($this->builder->has('featured_expiration'));
    }

    public function testDoesNotAddFeaturedExpirationFieldWhenFeaturedToggleIsMissing(): void
    {
        $type = new ContentType();
        $type->setClass(Article::class);
        $type->setOption('featured_expiration', true);

        $builder = new FormBuilder(
            'form',
            Content::class,
            new EventDispatcher(),
            new FormFactory(new FormRegistry([], new ResolvedFormTypeFactory()))
        );

        $event = new BuilderEvent($type, new Document(null), $builder, []);

        $this->listener->onPostBuild($event);

        self::assertFalse($builder->has('featured_expiration'));
    }
}
