<?php

namespace Integrated\Bundle\FormTypeBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigBodyClass extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('bodyClass', [$this, 'bodyClass']),
        ];
    }

    public static function bodyClass($bodyClass)
    {
        preg_match_all('/(?:[^_]*_\s*){2}(.*)/', $bodyClass, $stripped);
        if (count($stripped) > 1) {
            $bodyClass = $stripped[1][0];
        }
        return $bodyClass;
    }
}
