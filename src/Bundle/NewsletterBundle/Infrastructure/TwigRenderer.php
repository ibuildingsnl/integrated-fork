<?php

namespace Integrated\Bundle\NewsletterBundle\Infrastructure;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\ContentFetcher;
use Integrated\Bundle\NewsletterBundle\Service\Renderer;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Twig\Environment;

final class TwigRenderer implements Renderer
{
    public function __construct(
        private readonly ContentFetcher $content,
        private readonly ThemeManager $themes,
        private readonly Environment $twig,
    ) {
    }

    public function render(Newsletter $newsletter): string
    {
        return $this->twig->render(
            $this->themes->locateTemplate('content/newsletter/show.html.twig')
                ?: '@IntegratedNewsletter/fallback.html.twig',
            [
                'newsletter' => $newsletter,
                'content' => $this->content->fetchFor($newsletter),
            ]
        );
    }
}
