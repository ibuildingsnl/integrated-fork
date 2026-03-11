<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Support;

use Integrated\Bundle\FormTypeBundle\Twig\TwigColorTools;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;
use Symfony\UX\TwigComponent\TwigComponentBundle;

abstract class AdminComponentKernelTestCase extends KernelTestCase
{
    use InteractsWithTwigComponents;

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new AdminComponentTestKernel('test', true);
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
        parent::tearDown();
    }

    protected function renderTwigString(string $template, array $context = []): string
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = static::getContainer()->get('twig');

        return $twig->createTemplate($template)->render($context);
    }
}

final class AdminComponentTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new TwigComponentBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'integrated-admin-component-render-tests',
            'test' => true,
            'router' => ['utf8' => true],
            'http_method_override' => false,
        ]);

        $container->extension('twig', [
            'strict_variables' => true,
            'paths' => [
                self::projectDir().'/vendor/integrated/integrated/src/Bundle/ContentBundle/Resources/views' => 'IntegratedContent',
            ],
        ]);

        $services = $container->services()->defaults()->autowire(true)->autoconfigure(true)->public();

        foreach (AdminComponentContract::componentClasses() as $componentClass) {
            $services->set($componentClass)->tag('twig.component');
        }

        $services->set(TwigColorTools::class)->tag('twig.extension');
        $services->set(PaginationFooterTestTwigExtension::class)->tag('twig.extension');
    }

    public function getCacheDir(): string
    {
        $suffix = md5((string) @filemtime(__FILE__).(string) @filemtime(__DIR__.'/AdminComponentContract.php'));

        return sys_get_temp_dir().'/integrated_content_bundle_component_tests/'.$suffix.'/cache';
    }

    public function getLogDir(): string
    {
        $suffix = md5((string) @filemtime(__FILE__).(string) @filemtime(__DIR__.'/AdminComponentContract.php'));

        return sys_get_temp_dir().'/integrated_content_bundle_component_tests/'.$suffix.'/logs';
    }

    public function getProjectDir(): string
    {
        return self::projectDir();
    }

    private static function projectDir(): string
    {
        return \dirname(__DIR__, 8);
    }
}

final class PaginationFooterTestPager implements \IteratorAggregate
{
    public function getIterator(): \Traversable
    {
        yield 1;
        yield 2;
    }
}

final class PaginationFooterTestTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'knp_pagination_render',
                static function (mixed $pagination, string $template): string {
                    return sprintf(
                        '<nav class="pagination-test" data-template="%s"><a class="pagination-page-1">1</a><a class="pagination-page-2">2</a></nav>',
                        htmlspecialchars($template, ENT_QUOTES)
                    );
                },
                ['is_safe' => ['html']]
            ),
        ];
    }
}
