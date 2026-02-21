<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Locator;

use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Symfony\Component\Finder\Finder;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class LayoutLocator
{
    public function __construct(private readonly ThemeManager $themeManager)
    {
    }

    /**
     * @return array<string, string>
     */
    public function getLayouts(string $theme, ?string $directory = null): array
    {
        $layouts = [];

        foreach ($this->themeManager->getThemes() as $id => $theme2) {
            if ($theme === $id
                || \in_array($id, $this->themeManager->getTheme($theme)->getFallback())
                || $id === 'default') {
                foreach ($theme2->getPaths() as $resource) {
                    foreach ($this->themeManager->locateResources($resource) as $path) {
                        $path .= $directory;
                        if (is_dir($path)) {
                            $finder = new Finder();
                            $finder->files()->in($path)->depth(0)->name('*.html.twig');

                            /** @var \Symfony\Component\Finder\SplFileInfo $file */
                            foreach ($finder as $file) {
                                $f = fopen($file, 'r');
                                $line = fgets($f);
                                fclose($f);
                                if (str_starts_with($line, '{#')) {
                                    preg_match('/(?<=\{# Template name: )(.*?)(?=\ #})/', $line, $matchedLine);
                                    $layouts[$matchedLine[0]] = $file->getRelativePathname();
                                } else {
                                    $layouts[$file->getRelativePathname()] = $file->getRelativePathname();
                                }
                            }
                        }
                    }
                }
            }
        }

        return $layouts;
    }
}
