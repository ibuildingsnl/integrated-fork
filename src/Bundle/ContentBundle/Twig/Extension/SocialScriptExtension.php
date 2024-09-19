<?php

namespace Integrated\Bundle\ContentBundle\Twig\Extension;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Metadata;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SocialScriptExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('get_social_media_scripts', [$this, 'getSocialMediaScripts']),
        ];
    }

    public function getSocialMediaScripts(MetaData $meta): ?string
    {
        $scripts = [];

        $socialScripts = [
            'embed_twitter' => 'https://platform.twitter.com/widgets.js',
            'embed_reddit' => 'https://embed.reddit.com/widgets.js',
            'embed_tiktok' => 'https://www.tiktok.com/embed.js',
            'embed_instagram' => 'https://platform.instagram.com/en_US/embeds.js',
            'embed_facebook' => 'https://connect.facebook.net/nl_NL/sdk.js#xfbml=1&version=v19.0',
        ];

        foreach ($socialScripts as $key => $url) {
            if ($meta->get($key)) {
                $scripts[] = "<script src=\"{$url}\"></script>";
            }
        }

        return $scripts ? implode("\n", $scripts) : null;
    }
}
