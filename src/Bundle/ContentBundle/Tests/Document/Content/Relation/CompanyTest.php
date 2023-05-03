<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Document\Content\Relation;

use Integrated\Bundle\ContentBundle\Document\Content\Relation\Company;
use Integrated\Bundle\ContentBundle\Tests\Document\Content\ContentTestTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class CompanyTest extends TestCase
{
    use ContentTestTrait;
    use RelationTestTrait;

    /**
     * @var Company
     */
    private $company;

    /**
     * Setup the test.
     */
    protected function setUp(): void
    {
        $this->company = new Company();
    }

    /**
     * Test get- and setEmail function.
     */
    public function testGetAndSetEmailFunction()
    {
        $email = 'email';
        $this->assertEquals($email, $this->company->setEmail($email)->getEmail());
    }

    /**
     * Test get- and setWebsite function.
     */
    public function testGetAndSetWebsiteFunction()
    {
        $website = 'http://www.website.com';
        $this->assertEquals($website, $this->company->setWebsite($website)->getWebsite());
    }

    /**
     * Test get- and setAddresses function.
     */
    public function testGetAndSetAddressesFunction()
    {
        $addresses = [
            $this->createMock('Integrated\Bundle\ContentBundle\Document\Content\Embedded\Address'),
            $this->createMock('Integrated\Bundle\ContentBundle\Document\Content\Embedded\Address'),
        ];

        $this->assertSame($addresses, $this->company->setAddresses($addresses)->getAddresses());
    }

    /**
     * Test get- and setName function.
     */
    public function testGetAndSetNameFunction()
    {
        $name = 'name';
        $this->assertEquals($name, $this->company->setName($name)->getName());
    }

    /**
     * Test get- and setLogo function.
     */
    public function testGetAndSetLogoFunction()
    {
        /* @var $logo \Integrated\Common\Content\Document\Storage\Embedded\StorageInterface | MockObject */
        $logo = $this->createMock('Integrated\Common\Content\Document\Storage\Embedded\StorageInterface');
        $this->assertSame($logo, $this->company->setLogo($logo)->getLogo());
    }

    /**
     * Test toString function.
     */
    public function testToStringFunction()
    {
        $name = 'Name';
        $this->assertEquals($name, (string) $this->company->setName($name));
    }

    /**
     * {@inheritdoc}
     */
    protected function getContent()
    {
        return $this->company;
    }
}
