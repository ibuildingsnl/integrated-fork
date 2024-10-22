<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\EventListener\Objects;

use Integrated\Bundle\ContentBundle\Event\ContentRenderEvent;
use Integrated\Bundle\ThemeBundle\Exception\CircularFallbackException;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Twig\Environment;

/**
 * @author Marijn Otte <marijn@e-active.nl>
 */
class ContentYoutubeListener
{
    public function __construct(
        private readonly ThemeManager $themeManager,
        private readonly Environment $templating,
        private readonly string $env,
    ) {
    }

    /** @throws \Exception */
    public function process(ContentRenderEvent $contentEvent): void
    {
        try {
            $content = preg_replace_callback(
                '/\[object.*?type=\"youtube\".*?id\="(.+?)".*?\]/',
                function ($matches) {
                    return $this->getTemplate($matches[1]);
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

    /** @throws CircularFallbackException */
    protected function getTemplate(string $youtubeId): ?string
    {
        $template = $this->themeManager->locateTemplate('objects/youtube/default.html.twig');

        return $this->templating->render(
            $template,
            ['youtubeId' => $youtubeId]
        );
    }
}
