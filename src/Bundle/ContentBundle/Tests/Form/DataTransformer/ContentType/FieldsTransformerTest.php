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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\DataTransformerInterface;
use stdClass;
use Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\Field;
use Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\CustomField;
use Integrated\Common\ContentType\ContentTypeFieldInterface;
use Integrated\Bundle\ContentBundle\Form\DataTransformer\ContentType\FieldsTransformer;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class FieldsTransformerTest extends TestCase
{
    /**
     * @var FieldsTransformer
     */
    protected $fieldTransformer;

    /**
     * Setup the test.
     */
    protected function setUp(): void
    {
        $this->fieldTransformer = new FieldsTransformer();
    }

    /**
     * Test instanceOf.
     */
    public function testInstanceOf()
    {
        $this->assertInstanceOf(DataTransformerInterface::class, $this->fieldTransformer);
    }

    /**
     * Test transform function with empty data.
     *
     * @param mixed $input
     * @dataProvider getInvalidTransformData
     */
    public function testTransformFunctionWithInvalidData($input)
    {
        $output = ['default' => [], 'custom' => []];
        $this->assertSame($output, $this->fieldTransformer->transform($input));
    }

    /**
     * Test transform function with data.
     *
     * @param array $input
     * @param array $output
     * @dataProvider getValidTransformData
     */
    public function testTransformFunctionWithValidData(array $input, array $output)
    {
        $this->assertEquals($output, $this->fieldTransformer->transform($input));
    }

    /**
     * Test reverseTransform function with invalid data.
     *
     * @param mixed $input
     * @dataProvider getInvalidReverseTransformData
     */
    public function testReverseTransformFunctionWithInvalidData($input)
    {
        $this->assertSame([], $this->fieldTransformer->reverseTransform($input));
    }

    /**
     * Test reverseTransform function with valid data.
     *
     * @param array $input
     * @dataProvider getValidReverseTransformData
     */
    public function testReverseTransformFunctionWithValidData(array $input)
    {
        $this->assertSame(
            array_merge($input['default'], $input['custom']),
            $this->fieldTransformer->reverseTransform($input)
        );
    }

    /**
     * @return array
     */
    public function getInvalidTransformData()
    {
        return [
            'emptyData' => [
                null,
            ],
            'invalidDataString' => [
                'string',
            ],
            'invalidDataStdClass' => [
                $this->createMock(stdClass::class),
            ],
            'invalidDataArray' => [
                [],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getValidTransformData()
    {
        /** @var Field|\PHPUnit_Framework_MockObject_MockObject $default1 */
        $default1 = $this->createMock(Field::class);
        $default1
            ->expects($this->once())
            ->method('getName')
            ->willReturn('name')
        ;

        /** @var Field|\PHPUnit_Framework_MockObject_MockObject $default2 */
        $default2 = $this->createMock(Field::class);
        $default2
            ->expects($this->once())
            ->method('getName')
            ->willReturn('name2')
        ;

        /** @var Field|\PHPUnit_Framework_MockObject_MockObject $duplicateDefault */
        $duplicateDefault = $this->createMock(Field::class);
        $duplicateDefault
            ->expects($this->once())
            ->method('getName')
            ->willReturn('name')
        ;

        /** @var CustomField|\PHPUnit_Framework_MockObject_MockObject $custom1 */
        $custom1 = $this->createMock(CustomField::class);

        /** @var CustomField|\PHPUnit_Framework_MockObject_MockObject $custom2 */
        $custom2 = $this->createMock(CustomField::class);

        return [
            'validData' => [
                'input' => [
                    $default1,
                    $default2,
                    $duplicateDefault,
                    $custom1,
                    $custom2,
                ],
                'output' => [
                    'default' => [
                        'name' => $duplicateDefault,
                        'name2' => $default2,
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
     * @return array
     */
    public function getInvalidReverseTransformData()
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
     * @return array
     */
    public function getValidReverseTransformData()
    {
        return [
            'onlyDefaultValues' => [
                [
                    'default' => [
                        $this->createMock(ContentTypeFieldInterface::class),
                        $this->createMock(ContentTypeFieldInterface::class),
                    ],
                    'custom' => [],
                ],
            ],
            'onlyCustomValues' => [
                [
                    'default' => [],
                    'custom' => [
                        $this->createMock(ContentTypeFieldInterface::class),
                    ],
                ],
            ],
            'defaultAndCustomValues' => [
                [
                    'default' => [
                        $this->createMock(ContentTypeFieldInterface::class),
                        $this->createMock(ContentTypeFieldInterface::class),
                    ],
                    'custom' => [
                        $this->createMock(ContentTypeFieldInterface::class),
                    ],
                ],
            ],
        ];
    }
}
