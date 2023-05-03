<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Form\DataTransformer\ContentType\Field;

use Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\CustomField;
use Integrated\Bundle\ContentBundle\Form\DataTransformer\ContentType\Field\CustomTransformer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\DataTransformerInterface;

class CustomTransformerTest extends TestCase
{
    protected CustomTransformer $customTransformer;

    protected function setUp(): void
    {
        $this->customTransformer = new CustomTransformer();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(DataTransformerInterface::class, $this->customTransformer);
    }

    /**
     * Test transform function.
     */
    #[DataProvider('getTransformData')]
    public function testTransformFunction($input, array $output)
    {
        $this->assertSame($output, $this->customTransformer->transform($input));
    }

    public static function getTransformData(): array
    {
        $field = new CustomField();

        $field->setName('name');
        $field->setType('type');
        $field->setOptions([
            'label' => 'label',
            'required' => false,
        ]);

        return [
            'emptyData' => [
                'input' => null,
                'output' => [],
            ],
            'invalidData' => [
                'input' => 'string',
                'output' => [],
            ],
            'validData' => [
                'input' => $field,
                'output' => [
                    'name' => 'name',
                    'type' => 'type',
                    'label' => 'label',
                    'required' => false,
                ],
            ],
        ];
    }

    /**
     * Test reverseTransform function with invalid data.
     */
    #[DataProvider('getInvalidReverseTransformData')]
    public function testReverseTransformFunctionWithInvalidData($input)
    {
        $this->assertNull($this->customTransformer->reverseTransform($input));
    }

    public static function getInvalidReverseTransformData(): array
    {
        return [
            'emptyData' => [
                'input' => null,
            ],
            'invalidData' => [
                'input' => 'string',
            ],
            'incompleteDataNoLabel' => [
                'input' => [
                    'type' => 'text',
                    'required' => true,
                ],
            ],
            'incompleteDataNoType' => [
                'input' => [
                    'label' => 'label',
                    'required' => true,
                ],
            ],
        ];
    }

    /**
     * Test reverseTransform function with valid data.
     */
    #[DataProvider('getValidReverseTransformData')]
    public function testReverseTransformFunctionWithValidData(array $input)
    {
        /** @var \Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\CustomField $output */
        $output = $this->customTransformer->reverseTransform($input);

        $this->assertInstanceOf('\Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\CustomField', $output);
        $this->assertSame($input['label'], $output->getLabel());
        $this->assertSame($input['type'], $output->getType());

        if (!empty($input['required'])) {
            $options = $output->getOptions();
            $this->assertTrue($options['required']);
        }

        if (isset($input['name'])) {
            $this->assertSame($input['name'], $output->getName());
        }
    }

    public static function getValidReverseTransformData(): array
    {
        return [
            'requiredField' => [
                [
                    'label' => 'label',
                    'type' => 'type',
                    'required' => true,
                ],
            ],
            'fieldWithName' => [
                [
                    'label' => 'label',
                    'type' => 'type',
                    'name' => 'name',
                ],
            ],
        ];
    }
}
