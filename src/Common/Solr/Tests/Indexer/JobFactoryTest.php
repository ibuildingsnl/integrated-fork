<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Tests\Indexer;

use PHPUnit\Framework\TestCase;
use Integrated\Common\Solr\Tests\Fixtures\Object1;
use Integrated\Common\Solr\Tests\Fixtures\__CG__\ProxyObject;
use Integrated\Common\Solr\Tests\Fixtures\Object2;
use Integrated\Common\Solr\Exception\OutOfBoundsException;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Solr\Indexer\JobFactory;
use Integrated\Common\Solr\Indexer\JobFactoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class JobFactoryTest extends TestCase
{
    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var string
     */
    private $format = 'json';

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    public function testInterface()
    {
        $this->assertInstanceOf(JobFactoryInterface::class, $this->getInstance());
    }

    /**
     * @dataProvider createAddProvider
     */
    public function testCreateAdd($action, ContentInterface $content, $id, $class, $format)
    {
        $this->format = $format;

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->identicalTo($content), $this->equalTo($format))
            ->willReturn('this-is-the-data');

        $job = $this->getInstance()->create($action, $content);

        self::assertEquals('ADD', $job->getAction());

        self::assertEquals($id, $job->getOption('document.id'));
        self::assertEquals('this-is-the-data', $job->getOption('document.data'));
        self::assertEquals($class, $job->getOption('document.class'));
        self::assertEquals($format, $job->getOption('document.format'));
    }

    /**
     * @return array
     */
    public function createAddProvider()
    {
        return [
            'lower case' => [
                'add',
                new Object1(),
                'type1-id1',
                Object1::class,
                'json',
            ],
            'upper case' => [
                'ADD',
                new Object1(),
                'type1-id1',
                Object1::class,
                'json',
            ],
            'doctrine proxy' => [
                'add',
                new ProxyObject(),
                'proxy-type-proxy-id',
                'ProxyObject', // everything before and including __GC__ should be stripped
                'json',
            ],
            'format' => [
                'ADD',
                new Object2(),
                'type2-id2',
                Object2::class,
                'xml',
            ],
        ];
    }

    /**
     * @dataProvider createDeleteProvider
     */
    public function testCreateDelete($action, ContentInterface $content, $id)
    {
        $job = $this->getInstance()->create($action, $content);

        self::assertEquals('DELETE', $job->getAction());
        self::assertEquals($id, $job->getOption('id'));
    }

    /**
     * @return array
     */
    public function createDeleteProvider()
    {
        return [
            'lower case' => [
                'delete',
                new Object1(),
                'type1-id1',
            ],
            'upper case' => [
                'DELETE',
                new Object2(),
                'type2-id2',
            ],
        ];
    }

    public function testCreateInvalidAction()
    {
        $this->expectException(OutOfBoundsException::class);

        $this->getInstance()->create('none-existing-action', $this->getContent());
    }

    /**
     * @return JobFactory
     */
    public function getInstance()
    {
        return new JobFactory($this->serializer, $this->format);
    }

    /**
     * @return ContentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContent()
    {
        return $this->createMock(ContentInterface::class);
    }
}
