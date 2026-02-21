<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Form\DataTransformer\ContentType;

use Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\CustomField;
use Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\Field;
use Integrated\Bundle\ContentBundle\Form\DataTransformer\ContentType\FieldsTransformer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\DataTransformerInterface;

class FieldsTransformerTest extends TestCase
{
    protected FieldsTransformer $fieldTransformer;

    protected function setUp(): void
    {
        $this->fieldTransformer = new FieldsTransformer();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(DataTransformerInterface::class, $this->fieldTransformer);
    }

    /**
     * Test transform function with empty data.
     */
    #[DataProvider('getInvalidTransformData')]
    public function testTransformFunctionWithInvalidData($input)
    {
        $output = ['default' => [], 'custom' => []];

        $this->assertSame($output, $this->fieldTransformer->transform($input));
    }

    public static function getInvalidTransformData(): array
    {
        return [
            'emptyData' => [
                null,
            ],
            'invalidDataString' => [
                'string',
            ],
            'invalidDataStdClass' => [
                new \stdClass(),
            ],
            'invalidDataArray' => [
                [],
            ],
        ];
    }

    /**
     * Test transform function with data.
     */
    #[DataProvider('getValidTransformData')]
    public function testTransformFunctionWithValidData(array $input, array $output)
    {
        $this->assertEquals($output, $this->fieldTransformer->transform($input));
    }

    public static function getValidTransformData(): array
    {
        $field1 = new Field();
        $field1->setName('name1');

        $field2 = new Field();
        $field2->setName('name2');

        $field3 = new Field();
        $field3->setName('name1'); // duplicate of field1

        $custom1 = new CustomField();
        $custom2 = new CustomField();

        return [
            'validData' => [
                'input' => [
                    $field1,
                    $field2,
                    $field3,
                    $custom1,
                    $custom2,
                ],
                'output' => [
                    'default' => [
                        'name1' => $field3,
                        'name2' => $field2,
                    ],
                    'custom' => [
                        $custom1,
                        $custom2,
                    ],
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
        $this->assertSame([], $this->fieldTransformer->reverseTransform($input));
    }

    public static function getInvalidReverseTransformData(): array
    {
        return [
            'emptyData' => [
                null,
            ],
            'inValidKeys' => [
                [
                    'inValidKey1' => 'string',
                    'inValidKey2' => [],
                ],
            ],
            'emptyValues' => [
                [
                    'default' => null,
                    'custom' => null,
                ],
            ],
            'inValidValues' => [
                [
                    'default' => 1,
                    'custom' => 'string',
                ],
            ],
            'inValidValuesInArray' => [
                [
                    'default' => [
                        'string',
                    ],
                    'custom' => [
                        [],
                    ],
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
        $this->assertSame(array_merge($input['default'], $input['custom']), $this->fieldTransformer->reverseTransform($input));
    }

    public static function getValidReverseTransformData(): array
    {
        return [
            'onlyDefaultValues' => [
                [
                    'default' => [
                        new Field(),
                        new Field(),
                    ],
                    'custom' => [],
                ],
            ],
            'onlyCustomValues' => [
                [
                    'default' => [],
                    'custom' => [
                        new CustomField(),
                    ],
                ],
            ],
            'defaultAndCustomValues' => [
                [
                    'default' => [
                        new Field(),
                        new Field(),
                    ],
                    'custom' => [
                        new CustomField(),
                    ],
                ],
            ],
        ];
    }
}
