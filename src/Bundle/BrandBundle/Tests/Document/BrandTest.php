<?php

namespace Integrated\Bundle\BrandBundle\Tests\Document;

use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use PHPUnit\Framework\TestCase;

class BrandTest extends TestCase
{
    public function testHasChannelLinkReturnsTrueForSameInstance(): void
    {
        $brand = new Brand();
        $link = new ChannelLink(new ChannelType('website', 'Website'), null, false);
        $brand->addChannelLink($link);

        self::assertTrue($brand->hasChannelLink($link));
    }

    public function testHasChannelLinkReturnsTrueForSameIdentifier(): void
    {
        $brand = new Brand();
        $brandLink = new ChannelLink(new ChannelType('website', 'Website'), null, false);
        $otherReferenceToSameLink = new ChannelLink(new ChannelType('newsletter', 'Newsletter'), null, false);

        $this->setChannelLinkId($brandLink, 'link-1');
        $this->setChannelLinkId($otherReferenceToSameLink, 'link-1');

        $brand->addChannelLink($brandLink);

        self::assertTrue($brand->hasChannelLink($otherReferenceToSameLink));
    }

    public function testHasChannelLinkReturnsFalseForUnknownLink(): void
    {
        $brand = new Brand();
        $knownLink = new ChannelLink(new ChannelType('website', 'Website'), null, false);
        $unknownLink = new ChannelLink(new ChannelType('newsletter', 'Newsletter'), null, false);
        $brand->addChannelLink($knownLink);

        self::assertFalse($brand->hasChannelLink($unknownLink));
    }

    private function setChannelLinkId(ChannelLink $link, string $id): void
    {
        $property = new \ReflectionProperty(ChannelLink::class, 'id');
        $property->setAccessible(true);
        $property->setValue($link, $id);
    }
}
