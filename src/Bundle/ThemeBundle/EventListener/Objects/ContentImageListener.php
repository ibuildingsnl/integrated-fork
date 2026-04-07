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

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Event\ContentRenderEvent;
use Integrated\Bundle\SlugBundle\Slugger\SluggerInterface;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Twig\Environment;

/**
 * @author Michael Jongman <michael@e-active.nl>
 */
class ContentImageListener
{
    public function __construct(
        private readonly ThemeManager $themeManager,
        private readonly DocumentManager $documentManager,
        private readonly Environment $templating,
        private readonly SluggerInterface $slugger,
        private readonly string $env,
    ) {
    }

    /** @throws \Exception */
    public function replaceImages(ContentRenderEvent $contentEvent): void
    {
        try {
            $imageIndex = 0;
            $documents = $this->loadImages($contentEvent->getContent());
            $content = preg_replace_callback(
                '/\<img.*?data\-integrated\-id\="(.+?)".*?\>/',
                function ($matches) use (&$imageIndex, $documents) {
                    ++$imageIndex;

                    return $this->findImages($matches, $documents, $imageIndex);
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
     * @return array<string, Content>
     */
    protected function loadImages(string $content): array
    {
        if (!preg_match_all('/\<img.*?data\-integrated\-id\="(.+?)".*?\>/', $content, $matches)) {
            return [];
        }

        $ids = array_values(array_unique(array_filter(array_map('trim', $matches[1]))));
        if ($ids === []) {
            return [];
        }

        $documents = $this->documentManager
            ->getRepository(Content::class)
            ->findBy(['_id' => ['$in' => $ids]]);

        $indexedDocuments = [];

        foreach ($documents as $document) {
            $indexedDocuments[(string) $document->getId()] = $document;
        }

        return $indexedDocuments;
    }

    /**
     * @param array<string, Content> $documents
     */
    protected function findImages(array $matches, array $documents, int $imageIndex = 1): ?string
    {
        $file = $documents[$matches[1]] ?? null;
        if ($file instanceof Content) {
            $class = '';
            $width = '';
            $height = '';
            $style = '';
            $loading = $imageIndex === 1 ? 'eager' : 'lazy';
            $fetchPriority = $imageIndex === 1 ? 'high' : 'low';
            $decoding = 'async';
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

            return $this->getTemplate($file, $class, $width, $height, $style, $loading, $decoding, $fetchPriority);
        }

        return $matches[0];
    }

    protected function getTemplate(
        Content $file,
        string $class = '',
        string $width = '',
        string $height = '',
        string $style = '',
        string $loading = 'lazy',
        string $decoding = 'async',
        string $fetchPriority = 'low',
    ): ?string {
        if ($template = $this->getViewFromClass($class)) {
            return $this->templating->render(
                $template,
                [
                    'document' => $file,
                    'class' => $class,
                    'width' => $width,
                    'height' => $height,
                    'style' => $style,
                    'loading' => $loading,
                    'decoding' => $decoding,
                    'fetchPriority' => $fetchPriority,
                ]
            );
        }

        return null;
    }

    protected function getViewFromClass(string $class = ''): string
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
