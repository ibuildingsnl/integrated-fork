<?php

declare(strict_types=1);

namespace Integrated\Bundle\FormTypeBundle\Tests\Resources;

use Integrated\Bundle\FormTypeBundle\Form\Type\EditorType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EditorTypeViewTest extends TestCase
{
    public function testBuildViewExposesModeAndContentStyles(): void
    {
        $type = new EditorType(['/assets/editor.css']);
        $view = new FormView();

        $type->buildView($view, $this->createStub(FormInterface::class), [
            'mode' => 'compact',
        ]);

        self::assertSame('compact', $view->vars['mode']);
        self::assertSame(['/assets/editor.css'], $view->vars['content_styles']);
    }

    public function testConfigureOptionsDefaultsEmptyDataToEmptyString(): void
    {
        $type = new EditorType([]);
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        self::assertSame('', $options['empty_data']);
        self::assertSame('default', $options['mode']);
    }
}
