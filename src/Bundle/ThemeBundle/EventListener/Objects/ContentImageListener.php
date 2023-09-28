<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ThemeBundle\EventListener\Objects;

use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Event\ContentEvent;
use Integrated\Bundle\SlugBundle\Slugger\SluggerInterface;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Twig\Environment;

/**
 * @author Michael Jongman <michael@e-active.nl>
 */
class ContentImageListener
{
    /**
     * @var ThemeManager
     */
    protected $themeManager;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Environment
     */
    protected $templating;

    /**
     * @var SluggerInterface
     */
    protected $slugger;

    /**
     * @var string
     */
    protected $env;

    /**
     * @param string $env
     */
    public function __construct(
        ThemeManager $themeManager,
        ObjectManager $objectManager,
        Environment $templating,
        SluggerInterface $slugger,
        $env
    ) {
        $this->themeManager = $themeManager;
        $this->objectManager = $objectManager;
        $this->templating = $templating;
        $this->slugger = $slugger;
        $this->env = $env;
    }

    /**
     * @throws \Exception
     */
    public function replaceImages(ContentEvent $contentEvent)
    {
        try {
            $content = preg_replace_callback(
                '/\<img.*?data\-integrated\-id\="(.+?)".*?\>/',
                function ($matches) {
                    return $this->findImages($matches);
                },
                $contentEvent->getContent()
            );

            $contentEvent->setContent($content);
        } catch (\Exception $e) {
            if ('prod' !== $this->env) {
                throw $e;
            }
        }
    }

    /**
     * @param array $matches
     *
     * @return string|null
     */
    protected function findImages($matches)
    {
        if ($file = $this->objectManager->find(Content::class, $matches[1])) {
            $class = '';
            $width = '';
            $height = '';
            $style = '';
            if (preg_match('/class="(.*?)"/', $matches[0], $imgClass)) {
                $class = $imgClass[1];
            }
            if (preg_match('/width="(.*?)"/', $matches[0], $imgWidth)) {
                $width = $imgWidth[1];
            }
            if (preg_match('/height="(.*?)"/', $matches[0], $imgHeight)) {
                $height = $imgHeight[1];
            }
            if (preg_match('/style="(.*?)"/', $matches[0], $imgStyle)) {
                $style = $imgStyle[1];
            }

            return $this->getTemplate($file, $class, $width, $height, $style);
        }

        return $matches[0];
    }

    /**
     * @param string $class
     *
     * @return string|null
     */
    protected function getTemplate(Content $file, $class = '', $width = '', $height = '', $style = '')
    {
        if ($template = $this->getViewFromClass($class)) {
            return $this->templating->render(
                $template,
                ['document' => $file, 'class' => $class, 'width' => $width, 'height' => $height, 'style' => $style]
            );
        }

        return null;
    }

    /**
     * @param string $class
     *
     * @return string
     */
    protected function getViewFromClass($class = '')
    {
        if (preg_match('/template-image-(.*?)(\s|$)/', $class, $views)) {
            $view = $this->slugger->slugify($views[1], '_');

            if ($template = $this->themeManager->locateTemplate('objects/image/'.$view.'.html.twig')) {
                return $template;
            }
        }

        return $this->themeManager->locateTemplate('objects/image/default.html.twig');
    }
}
