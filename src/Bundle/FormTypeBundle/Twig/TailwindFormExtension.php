<?php
/**
 * This file is part of BraincraftedtailwindBundle.
 * (c) 2012-2013 by Florian Eckerstorfer.
 */

namespace Integrated\Bundle\FormTypeBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * tailwindFormExtension.
 *
 * @author     Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright  2012-2013 Florian Eckerstorfer
 * @license    http://opensource.org/licenses/MIT The MIT License
 *
 * @see       http://tailwind.braincrafted.com tailwind for Symfony2
 */
class TailwindFormExtension extends AbstractExtension
{
    /** @var string */
    private $style;

    /** @var int */
    private $widgetCol = 'sm:w-10/12 2xl:w-11/12';

    /** @var int */
    private $labelCol = 'sm:w-2/12 2xl:w-1/12';

    /** @var boolean */
    private $showLabel = true;

    /** @var boolean */
    private $showPlaceholder = true;

    /** @var int */
    private $simpleCol = false;

    /** @var array */
    private $settingsStack = [];

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('tailwind_set_style', [$this, 'setStyle']),
            new TwigFunction('tailwind_get_style', [$this, 'getStyle']),
            new TwigFunction('tailwind_set_widget_col', [$this, 'setWidgetCol']),
            new TwigFunction('tailwind_get_widget_col', [$this, 'getWidgetCol']),
            new TwigFunction('tailwind_set_label_col', [$this, 'setLabelCol']),
            new TwigFunction('tailwind_get_label_col', [$this, 'getLabelCol']),
            new TwigFunction('tailwind_set_simple_col', [$this, 'setSimpleCol']),
            new TwigFunction('tailwind_get_simple_col', [$this, 'getSimpleCol']),
            //NEW FUNCTIONS
            new TwigFunction('tailwind_set_show_label', [$this, 'setShowLabel']),
            new TwigFunction('tailwind_get_show_label', [$this, 'getShowLabel']),
            new TwigFunction('tailwind_set_show_placeholder', [$this, 'setShowPlaceholder']),
            new TwigFunction('tailwind_get_show_placeholder', [$this, 'getShowPlaceholder']),
            //TODO: FINISH THIS
            new TwigFunction('tailwind_backup_form_settings', [$this, 'backupFormSettings']),
            new TwigFunction('tailwind_restore_form_settings', [$this, 'restoreFormSettings']),
            new TwigFunction(
                'checkbox_row',
                null,
                ['is_safe' => ['html'], 'node_class' => 'Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode']
            ),
            new TwigFunction(
                'radio_row',
                null,
                ['is_safe' => ['html'], 'node_class' => 'Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode']
            ),
            new TwigFunction(
                'global_form_errors',
                null,
                ['is_safe' => ['html'], 'node_class' => 'Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode']
            ),
            new TwigFunction(
                'form_control_static',
                [$this, 'formControlStaticFunction'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twindigital_tailwind_form';
    }

    /**
     * Sets the style.
     *
     * @param string $style Name of the style
     */
    public function setStyle($style)
    {
        $this->style = $style;
    }

    /**
     * Returns the style.
     *
     * @return string Name of the style
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Sets the number of columns of widgets.
     *
     * @param int $widgetCol number of columns
     */
    public function setWidgetCol($widgetCol)
    {
        $this->widgetCol = $widgetCol;
    }

    /**
     * Returns the number of columns of widgets.
     *
     * @return int Number of columns.Class
     */
    public function getWidgetCol()
    {
        return $this->widgetCol;
    }

    /**
     * Sets the number of columns of labels.
     *
     * @param int $labelCol number of columns
     */
    public function setLabelCol($labelCol)
    {
        $this->labelCol = $labelCol;
    }

    /**
     * Returns the number of columns of labels.
     *
     * @return int number of columns
     */
    public function getLabelCol()
    {
        return $this->labelCol;
    }

    /**
     * Sets the value of Labels to true or false.
     *
     * @param bool $showLabel true or false
     */
    public function setShowLabel($showLabel)
    {
        $this->showLabel = $showLabel;
    }

    /**
     * Returns the value of true or false to show or hide Labels.
     *
     * @param bool $showLabel true or false
     */
    public function getShowLabel()
    {
        return $this->showLabel;
    }

    /**
     * Sets the value of Placeholders to true or false.
     *
     * @param bool $showPlaceholder true or false
     */
    public function setShowPlaceholder($showPlaceholder)
    {
        $this->showPlaceholder = $showPlaceholder;
    }

    /**
     * Returns the value of true or false to show or hide Placeholders.
     *
     * @param bool $showPlaceholder true or false
     */
    public function getShowPlaceholder()
    {
        return $this->showPlaceholder;
    }


    /**
     * Sets the number of columns of simple widgets.
     *
     * @param int $simpleCol number of columns
     */
    public function setSimpleCol($simpleCol)
    {
        $this->simpleCol = $simpleCol;
    }

    /**
     * Returns the number of columns of simple widgets.
     *
     * @return int number of columns
     */
    public function getSimpleCol()
    {
        return $this->simpleCol;
    }

    /**
     * Backup the form settings to the stack.
     *
     * @internal Should only be used at the beginning of form_start. This allows
     *           a nested subform to change its settings without affecting its
     *           parent form.
     */
    public function backupFormSettings()
    {
        $settings = [
            'style' => $this->style,
            'widgetCol' => $this->widgetCol,
            'labelCol' => $this->labelCol,
            'simpleCol' => $this->simpleCol,
            'showLabel' => $this->showLabel,
            'showPlaceholder' => $this->showPlaceholder,
        ];

        $this->settingsStack[] = $settings;
    }

    /**
     * Restore the form settings from the stack.
     *
     * @internal should only be used at the end of form_end
     *
     * @see backupFormSettings
     */
    public function restoreFormSettings()
    {
        if (\count($this->settingsStack) < 1) {
            return;
        }

        $settings = array_pop($this->settingsStack);

        $this->style = $settings['style'];
        $this->widgetCol = $settings['widgetCol'];
        $this->labelCol = $settings['labelCol'];
        $this->simpleCol = $settings['simpleCol'];
        $this->showLabel = $settings['showLabel'];
        $this->showPlaceholder = $settings['showPlaceholder'];
    }

    /**
     * @param string $label
     * @param string $value
     *
     * @return string
     */
    public function formControlStaticFunction($label, $value)
    {
        return sprintf(
            '<div class="form-group"><label class="w-full %s control-label">%s</label><div class="w-full %s"><p class="form-control-static">%s</p></div></div>',
            $this->getLabelCol(),
            $label,
            $this->getWidgetCol(),
            $value
        );
    }
}
