<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Document\Content;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\CustomFields;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Metadata;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Common\Content\ContentInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;

trait ContentTestTrait
{
    /**
     * Content should implement ContentInterface.
     */
    public function testInstanceOfContentInterface()
    {
        Assert::assertInstanceOf(ContentInterface::class, $this->getContent());
    }

    /**
     * Content should extend content.
     */
    public function testInstanceOfContent()
    {
        Assert::assertInstanceOf(Content::class, $this->getContent());
    }

    /**
     * Test get- and setId function.
     */
    public function testGetAndSetIdFunction()
    {
        $content = $this->getContent();
        $content->setId($id = 'abc123');

        Assert::assertEquals($id, $content->getId());
    }

    /**
     * Test get- and setContentType function.
     */
    public function testGetAndSetContentTypeFunction()
    {
        $content = $this->getContent();
        $content->setContentType($contentType = 'type');

        Assert::assertEquals($contentType, $content->getContentType());
    }

    /**
     * Test get- and setRelations function.
     */
    public function testGetAndSetRelationsFunction()
    {
        $content = $this->getContent();
        $content->setRelations($relations = [new Relation()]);

        Assert::assertEquals($relations, $content->getRelations());
    }

    /**
     * Test removeReference function.
     */
    public function testRemoveRelationFunction()
    {
        $content = $this->getContent();

        Assert::assertEmpty($content->getRelations());

        $content->addRelation($relation = new Relation());
        $content->removeRelation($relation);

        Assert::assertEmpty($content->getRelations());
    }

    /**
     * Test get- and setCreatedAt function.
     */
    public function testGetAndSetCreatedAtFunction()
    {
        $content = $this->getContent();
        $content->setCreatedAt($createdAt = new \DateTime());

        Assert::assertEquals($createdAt, $content->getCreatedAt());
    }

    /**
     * Test get- and setUpdated function.
     */
    public function testGetAndSetUpdatedAtFunction()
    {
        $content = $this->getContent();
        $content->setUpdatedAt($updatedAt = new \DateTime());

        Assert::assertEquals($updatedAt, $content->getUpdatedAt());
    }

    /**
     * Test publish time get- and setStartDate function.
     */
    public function testGetAndSetPublishTimeStartDateFunction()
    {
        $content = $this->getContent();
        $content->getPublishTime()->setStartDate($publishedAt = new \DateTime());

        Assert::assertSame($publishedAt, $content->getPublishTime()->getStartDate());
    }

    /**
     * Test publish time get- and setEndDate function.
     */
    public function testGetAndSetPublishTimeEndDateFunction()
    {
        $content = $this->getContent();
        $content->getPublishTime()->setEndDate($publishedUntil = new \DateTime());

        Assert::assertSame($publishedUntil, $content->getPublishTime()->getEndDate());
    }

    /**
     * Test get- and setDisabled function.
     */
    public function testGetAndSetDisabledFunction()
    {
        $content = $this->getContent();

        Assert::assertFalse($content->isDisabled());

        $content->setDisabled(true);

        Assert::assertTrue($content->isDisabled());

        $content->setDisabled(false);

        Assert::assertFalse($content->isDisabled());
    }

    /**
     * Test get- and setMetadata function.
     */
    public function testGetAndSetMetadataFunction()
    {
        $content = $this->getContent();
        $content->setMetadata($metadata = new Metadata());

        Assert::assertSame($metadata, $content->getMetadata());
    }

    /**
     * Test get- and setChannels function.
     */
    #[DataProvider('getChannels')]
    public function testGetAndSetChannelsFunction(array $channels)
    {
        $content = $this->getContent();
        $content->setChannels($channels);

        Assert::assertEquals($channels, $content->getChannels());
    }

    /**
     * Test addChannel function.
     */
    #[DataProvider('getChannels')]
    public function testAddChannelFunction(array $channels)
    {
        $content = $this->getContent();

        foreach ($channels as $channel) {
            $content->addChannel($channel);
            $content->addChannel($channel);

            Assert::assertContains($channel, $content->getChannels());
        }

        Assert::assertCount(\count($channels), $content->getChannels());
    }

    /**
     * Test removeChannel function.
     */
    public function testRemoveChannelFunction()
    {
        $content = $this->getContent();

        $content->addChannel($channel1 = new Channel());
        $content->addChannel($channel2 = new Channel());
        $content->removeChannel($channel2);

        Assert::assertContains($channel1, $content->getChannels());
        Assert::assertNotContains($channel2, $content->getChannels());
    }

    public function testHasChannelMatchesByChannelIdWhenInstanceDiffers(): void
    {
        $content = $this->getContent();

        $original = new Channel();
        $original->setId('channel-a');
        $content->addChannel($original);

        $differentInstanceSameId = new Channel();
        $differentInstanceSameId->setId('channel-a');

        Assert::assertTrue($content->hasChannel($differentInstanceSameId));
    }

    public function testGetPrimaryChannelHandlesMissingChannelsCollection(): void
    {
        $content = $this->getContent();
        $reflection = new \ReflectionProperty($content, 'channels');
        $reflection->setValue($content, null);

        Assert::assertNull($content->getPrimaryChannel());
        Assert::assertSame([], $content->getChannels());
    }

    public function testSetChannelsFallsBackPrimaryChannelToFirstSelectedChannelWhenCurrentPrimaryIsInvalid(): void
    {
        $content = $this->getContent();

        $invalidPrimary = new Channel();
        $invalidPrimary->setId('stale-primary');

        $firstChannel = new Channel();
        $firstChannel->setId('automationnl');

        $secondChannel = new Channel();
        $secondChannel->setId('bakkersinbedrijf');

        $content->setPrimaryChannel($invalidPrimary);
        $content->setChannels([$firstChannel, $secondChannel]);

        Assert::assertSame($firstChannel, $content->getPrimaryChannel());
        Assert::assertSame($firstChannel, $this->getStoredPrimaryChannel($content));
    }

    public function testSetPrimaryChannelFallsBackToFirstSelectedChannelWhenGivenChannelIsNotSelected(): void
    {
        $content = $this->getContent();

        $firstChannel = new Channel();
        $firstChannel->setId('automationnl');

        $secondChannel = new Channel();
        $secondChannel->setId('bakkersinbedrijf');

        $invalidPrimary = new Channel();
        $invalidPrimary->setId('stale-primary');

        $content->setChannels([$firstChannel, $secondChannel]);
        $content->setPrimaryChannel($invalidPrimary);

        Assert::assertSame($firstChannel, $content->getPrimaryChannel());
        Assert::assertSame($firstChannel, $this->getStoredPrimaryChannel($content));
    }

    /**
     * Test getCustomFields functions.
     */
    public function testCustomFieldsFunction()
    {
        $content = $this->getContent();
        $content->setCustomFields($fields = new CustomFields(['field1' => 'value1', 'field2' => 'value2']));

        Assert::assertInstanceOf(CustomFields::class, $content->getCustomFields());
        Assert::assertSame($fields, $content->getCustomFields());
    }

    public static function getChannels()
    {
        return [
            'single' => [[new Channel()]],
            'multiple' => [[new Channel(), new Channel()]],
        ];
    }

    private function getStoredPrimaryChannel(Content $content): ?Channel
    {
        $reflection = new \ReflectionProperty($content, 'primaryChannel');

        return $reflection->getValue($content);
    }

    /**
     * @return Content
     */
    abstract protected function getContent();
}
