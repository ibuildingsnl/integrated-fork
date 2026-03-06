<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\AssetBundle\Tests\Twig;

use Integrated\Bundle\AssetBundle\Manager\AssetManager;
use Integrated\Bundle\AssetBundle\Twig\Extension\JavascriptExtension;
use Integrated\Bundle\AssetBundle\Twig\Extension\StylesheetExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use Twig\Test\IntegrationTestCase;
use Twig\TwigFunction;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class IntegrationTest extends IntegrationTestCase
{
    #[DataProvider('provideLegacyTests')]
    public function testLegacyIntegration($file, $message, $condition, $templates, $exception, $outputs, $deprecation = ''): void
    {
        $this->testIntegration($file, $message, $condition, $templates, $exception, $outputs, $deprecation);
    }

    #[DataProvider('provideIntegrationTests')]
    public function testIntegration($file, $message, $condition, $templates, $exception, $outputs, $deprecation = ''): void
    {
        $templates += [
            '@IntegratedAsset/asset/javascripts.html.twig' => file_get_contents(
                __DIR__.'/../../Resources/views/asset/javascripts.html.twig'
            ),
            '@IntegratedAsset/asset/stylesheets.html.twig' => file_get_contents(
                __DIR__.'/../../Resources/views/asset/stylesheets.html.twig'
            ),
        ];

        $this->doIntegrationTest($file, $message, $condition, $templates, $exception, $outputs, $deprecation);
    }

    public static function provideIntegrationTests(): iterable
    {
        $instance = new static('testIntegration');

        return $instance->getTests('testIntegration');
    }

    public static function provideLegacyTests(): iterable
    {
        $instance = new static('testLegacyIntegration');

        return $instance->getTests('testLegacyIntegration', true);
    }

    public function getExtensions()
    {
        return [
            new StylesheetExtension(new AssetManager()),
            new JavascriptExtension(new AssetManager()),
        ];
    }

    protected function getTwigFunctions()
    {
        return [
            new TwigFunction('asset', function ($path) {
                return '/'.$path;
            }, ['is_safe' => ['html']]),
        ];
    }

    protected static function getFixturesDirectory(): string
    {
        return __DIR__.'/Fixtures/';
    }

    protected function getFixturesDir()
    {
        return static::getFixturesDirectory();
    }
}
