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
 */
class TailwindFormExtension extends AbstractExtension
{
    /** @var string */
    private $style;

    /** @var int */
    private $widgetCol = 'md:w-9/12 xl:w-10/12 3xl:w-11/12';

    /** @var int */
    private $labelCol = 'md:w-3/12 xl:w-2/12 3xl:w-1/12';

    /** @var bool */
    private $showLabel = true;

    /** @var int */
    private $simpleCol = false;

    /** @var string */
    private $icon = '';

    /** @var string */
    private $state = '';

    /** @var bool */
    private $showPlaceholder = true;

    /** @var array */
    private $settingsStack = [];

    public function getFunctions()
    {
        return [
            new TwigFunction('tailwind_set_style', $this->setStyle(...)),
            new TwigFunction('tailwind_get_style', $this->getStyle(...)),
            new TwigFunction('tailwind_set_widget_col', $this->setWidgetCol(...)),
            new TwigFunction('tailwind_get_widget_col', $this->getWidgetCol(...)),
            new TwigFunction('tailwind_set_label_col', $this->setLabelCol(...)),
            new TwigFunction('tailwind_get_label_col', $this->getLabelCol(...)),
            new TwigFunction('tailwind_set_simple_col', $this->setSimpleCol(...)),
            new TwigFunction('tailwind_get_simple_col', $this->getSimpleCol(...)),
            new TwigFunction('tailwind_set_show_label', $this->setShowLabel(...)),
            new TwigFunction('tailwind_get_show_label', $this->getShowLabel(...)),
            new TwigFunction('tailwind_set_icon', $this->setIcon(...)),
            new TwigFunction('tailwind_get_icon', $this->getIcon(...)),
            new TwigFunction('tailwind_set_state', $this->setState(...)),
            new TwigFunction('tailwind_get_state', $this->getState(...)),
            new TwigFunction('tailwind_set_show_placeholder', $this->setShowPlaceholder(...)),
            new TwigFunction('tailwind_get_show_placeholder', $this->getShowPlaceholder(...)),
            new TwigFunction('tailwind_backup_form_settings', $this->backupFormSettings(...)),
            new TwigFunction('tailwind_restore_form_settings', $this->restoreFormSettings(...)),
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
                $this->formControlStaticFunction(...),
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function getName()
    {
        return 'braincrafted_tailwind_form';
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
     */
    public function getShowLabel()
    {
        return $this->showLabel;
    }

    /**
     * Sets the value of Icon.
     *
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Returns the value of Icon.
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Sets the value of State to open or close settings by default.
     *
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Returns the value of State.
     */
    public function getState()
    {
        return $this->state;
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
            'icon' => $this->icon,
            'state' => $this->state,
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
        $this->icon = $settings['icon'];
        $this->state = $settings['state'];
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
