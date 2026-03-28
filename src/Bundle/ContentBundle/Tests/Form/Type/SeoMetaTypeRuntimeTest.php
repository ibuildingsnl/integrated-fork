<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Form\Type;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\SeoMeta;
use Integrated\Bundle\ContentBundle\Form\Type\SeoMetaType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

final class SeoMetaTypeRuntimeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([
                new SeoMetaType(),
            ], []),
        ];
    }

    public function testEmbeddedSeoMetaUsesPersistedValuesFromParentData(): void
    {
        $article = new Article();
        $article->setTitle('asdfasdf');
        $article->setSeoMetadata(new SeoMeta());
        $article->getSeoMetadata()?->setMetatitle('sadfasdfsadfasdf %%title%% %%separator%% %%channel%%');
        $article->getSeoMetadata()?->setMetadescription('custom description %%title%%');

        $form = $this->factory
            ->createBuilder(FormType::class, $article)
            ->add('seoMetadata', SeoMetaType::class, [
                'meta_title_fallback' => '%%title%% %%separator%% %%channel%%',
                'meta_description_fallback' => null,
            ])
            ->getForm();

        $view = $form->createView();

        self::assertSame(
            'sadfasdfsadfasdf %%title%% %%separator%% %%channel%%',
            $form->get('seoMetadata')->get('metaTitle')->getData()
        );
        self::assertSame(
            'sadfasdfsadfasdf %%title%% %%separator%% %%channel%%',
            $view['seoMetadata']['metaTitle']->vars['value']
        );
        self::assertSame(
            'custom description %%title%%',
            $form->get('seoMetadata')->get('metaDescription')->getData()
        );
        self::assertSame(
            'custom description %%title%%',
            $view['seoMetadata']['metaDescription']->vars['value']
        );
    }

    public function testStandaloneSeoMetaStillUsesPersistedValues(): void
    {
        $meta = new SeoMeta();
        $meta->setMetatitle('sadfasdfsadfasdf %%title%% %%separator%% %%channel%%');
        $meta->setMetadescription('custom description %%title%%');

        $form = $this->factory->create(SeoMetaType::class, $meta, [
            'meta_title_fallback' => '%%title%% %%separator%% %%channel%%',
            'meta_description_fallback' => 'fallback description',
        ]);
        $view = $form->createView();

        self::assertSame('sadfasdfsadfasdf %%title%% %%separator%% %%channel%%', $view['metaTitle']->vars['value']);
        self::assertSame('custom description %%title%%', $view['metaDescription']->vars['value']);
    }
}
