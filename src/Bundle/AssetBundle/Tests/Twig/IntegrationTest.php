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
    public function testLegacyIntegration(
        mixed $file,
        mixed $message,
        mixed $condition,
        mixed $templates,
        mixed $exception,
        mixed $outputs,
        mixed $deprecation = '',
    ): void {
        $this->testIntegration($file, $message, $condition, $templates, $exception, $outputs, $deprecation);
    }

    #[DataProvider('provideIntegrationTests')]
    public function testIntegration(
        mixed $file,
        mixed $message,
        mixed $condition,
        mixed $templates,
        mixed $exception,
        mixed $outputs,
        mixed $deprecation = '',
    ): void {
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

    /** @return iterable<int, array<int, mixed>> */
    public static function provideIntegrationTests(): iterable
    {
        $instance = new self('testIntegration');

        return $instance->getTests('testIntegration');
    }

    /** @return iterable<int, array<int, mixed>> */
    public static function provideLegacyTests(): iterable
    {
        $instance = new self('testLegacyIntegration');

        return $instance->getTests('testLegacyIntegration', true);
    }

    /** @return array<int, object> */
    public function getExtensions(): array
    {
        return [
            new StylesheetExtension(new AssetManager()),
            new JavascriptExtension(new AssetManager()),
        ];
    }

    /** @return array<int, TwigFunction> */
    protected function getTwigFunctions(): array
    {
        return [
            new TwigFunction('asset', function (string $path): string {
                return '/'.$path;
            }, ['is_safe' => ['html']]),
        ];
    }

    protected static function getFixturesDirectory(): string
    {
        return __DIR__.'/Fixtures/';
    }

    protected function getFixturesDir(): string
    {
        return static::getFixturesDirectory();
    }
}
