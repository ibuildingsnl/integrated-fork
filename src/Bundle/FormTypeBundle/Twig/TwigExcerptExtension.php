<?php

namespace Integrated\Bundle\FormTypeBundle\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExcerptExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('excerpt', array($this, 'excerptFilter'))
        ];
    }

    /**
     * @param Environment $twig
     * @param int $limit
     * @return null|string
     */
    public function excerptFilter($content, $limit)
    {
        $content = strip_tags($content);
        $excerpt = explode(' ', $content, $limit);
        if (count($excerpt) >= $limit) {
            array_pop($excerpt);
            $excerpt = implode(" ", $excerpt) . '...';
        } else {
            $excerpt = implode(" ", $excerpt);
        }
        $excerpt = preg_replace('`[[^]]*]`', '', $excerpt);

        return $excerpt;
    }
}