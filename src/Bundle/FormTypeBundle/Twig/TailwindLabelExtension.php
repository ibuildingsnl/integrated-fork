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
 */
class TailwindLabelExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        $options = ['pre_escape' => 'html', 'is_safe' => ['html']];

        return [
            new TwigFunction('label', $this->labelFunction(...), $options),
            new TwigFunction('label_primary', $this->labelPrimaryFunction(...), $options),
            new TwigFunction('label_success', $this->labelSuccessFunction(...), $options),
            new TwigFunction('label_info', $this->labelInfoFunction(...), $options),
            new TwigFunction('label_warning', $this->labelWarningFunction(...), $options),
            new TwigFunction('label_danger', $this->labelDangerFunction(...), $options),
        ];
    }

    /**
     * Returns the HTML code for a label.
     *
     * @param string $text The text of the label
     * @param string $type The type of label
     *
     * @return string The HTML code of the label
     */
    public function labelFunction($text, $type = 'default')
    {
        return \sprintf('<span class="label%s">%s</span>', $type ? ' label-'.$type : '', $text);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function labelPrimaryFunction($text)
    {
        return $this->labelFunction($text, 'primary');
    }

    /**
     * Returns the HTML code for a success label.
     *
     * @param string $text The text of the label
     *
     * @return string The HTML code of the label
     */
    public function labelSuccessFunction($text)
    {
        return $this->labelFunction($text, 'success');
    }

    /**
     * Returns the HTML code for a warning label.
     *
     * @param string $text The text of the label
     *
     * @return string The HTML code of the label
     */
    public function labelWarningFunction($text)
    {
        return $this->labelFunction($text, 'warning');
    }

    /**
     * Returns the HTML code for a important label.
     *
     * @param string $text The text of the label
     *
     * @return string The HTML code of the label
     */
    public function labelDangerFunction($text)
    {
        return $this->labelFunction($text, 'danger');
    }

    /**
     * Returns the HTML code for a info label.
     *
     * @param string $text The text of the label
     *
     * @return string The HTML code of the label
     */
    public function labelInfoFunction($text)
    {
        return $this->labelFunction($text, 'info');
    }

    public function getName(): string
    {
        return 'braincrafted_tailwind_label';
    }
}
