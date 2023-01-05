<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Locator;

use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Symfony\Component\Finder\Finder;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class LayoutLocator
{
    /**
     * @var ThemeManager
     */
    protected $themeManager;

    /**
     * @var array
     */
    private $layouts;

    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getLayouts($type)
    {
        if (!isset($this->layouts[$type])) {
            $this->layouts[$type] = [];

            foreach ($this->themeManager->getThemes() as $id => $theme) {
                foreach ($theme->getPaths() as $path) {
                    foreach ($this->themeManager->locateResources($path) as $path) {
                        $path = $path.'/blocks/'.$type;

                        if (is_dir($path)) {
                            $finder = new Finder();
                            $finder->files()->in($path)->name('*.html.twig');

                            /** @var \Symfony\Component\Finder\SplFileInfo $file */
                            foreach ($finder as $file) {
                                $f = fopen($file, 'r');
                                $line = fgets($f);
                                fclose($f);

                                if (str_starts_with($line, '{#')) {
                                    preg_match('/(?<=\{# Template name: )(.*?)(?=\ #})/', $line , $matchedLine );
                                    $this->layouts[$type][$matchedLine[0]] = $file->getRelativePathname();
                                } else {
                                    $this->layouts[$type][$file->getRelativePathname()] = $file->getRelativePathname();
                                }
                            }
                        }
                    }
                }
            }

            $this->layouts[$type] = array_unique($this->layouts[$type]);
        }

        return $this->layouts[$type];
    }
}
