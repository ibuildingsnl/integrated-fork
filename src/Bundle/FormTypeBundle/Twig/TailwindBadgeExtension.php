<?php
/**
 * This file is part of BraincraftedTailwindBundle.
 *
 * (c) 2012-2013 by Florian Eckerstorfer
 */

namespace Integrated\Bundle\FormTypeBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * TailwindLabelExtension.
 *
 * @category   TwigExtension
 *
 * @author     Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright  2012-2013 Florian Eckerstorfer
 * @license    http://opensource.org/licenses/MIT The MIT License
 *
 * @see       http://tailwind.braincrafted.com Tailwind for Symfony2
 */
class TailwindBadgeExtension extends AbstractExtension
{
    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'badge',
                [$this, 'badgeFunction'],
                ['pre_escape' => 'html', 'is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Returns the HTML code for a badge.
     *
     * @param string $text The text of the badge
     *
     * @return string The HTML code of the badge
     */
    public function badgeFunction($text)
    {
        return sprintf('<span class="badge">%s</span>', $text);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'braincrafted_tailwind_badge';
    }
}
