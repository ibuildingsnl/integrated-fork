<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Form\DataTransformer;

use Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\Field;
use Integrated\Common\Form\Mapping\AttributeInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ContentTypeField implements DataTransformerInterface
{
    /**
     * @var AttributeInterface
     */
    private $field;

    public function __construct(AttributeInterface $field)
    {
        $this->field = $field;
    }

    /**
     * @return array
     */
    public function transform($field): mixed
    {
        if ($field instanceof Field) {
            $options = $field->getOptions();

            if (!empty($options['value'])) {
                return [
                    'enabled' => true,
                    'required' => !empty($options['required']),
                    'value' => !empty($options['value'] ? 'checked' : false),
                ];
            } else {
                return [
                    'enabled' => true,
                    'required' => !empty($options['required']),
                ];
            }
        }

        return [];
    }

    /**
     * @return \Field|null
     */
    public function reverseTransform($value): ?Field
    {
        if (\is_array($value)) {
            if (!empty($value['enabled'])) {
                $field = new Field();

                $field->setName($this->field->getName());
                if (!empty($value['value'])) {
                    $field->setOptions(['required' => !empty($value['required']), 'value' => !empty($value['value']) ? 'checked' : false]);
                } else {
                    $field->setOptions(['required' => !empty($value['required'])]);
                }

                return $field;
            }
        }

        return null;
    }
}
