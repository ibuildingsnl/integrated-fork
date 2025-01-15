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

    public function testGetAndSetEmailFunction()
    {
        $email = 'email';
        $this->company->setEmail($email);
        $this->assertEquals($email, $this->company->getEmail());
    }

    public function testGetAndSetWebsiteFunction()
    {
        $website = 'http://www.website.com';
        $this->assertEquals($website, $this->company->setWebsite($website)->getWebsite());
    }

    public function testGetAndSetAddressesFunction()
    {
        $addresses = [
            $this->createMock('Integrated\Bundle\ContentBundle\Document\Content\Embedded\Address'),
            $this->createMock('Integrated\Bundle\ContentBundle\Document\Content\Embedded\Address'),
        ];

        $this->company->setAddresses($addresses);
        $this->assertSame($addresses, $this->company->getAddresses());
    }

    public function testGetAndSetNameFunction()
    {
        $name = 'name';
        $this->assertEquals($name, $this->company->setName($name)->getName());
    }

    public function testGetAndSetLogoFunction()
    {
        /* @var $logo \Integrated\Bundle\ContentBundle\Document\Content\Image | MockObject */
        $logo = $this->createMock('Integrated\Bundle\ContentBundle\Document\Content\Image');
        $this->company->setLogo($logo);
        $this->assertSame($logo, $this->company->getLogo());
    }

    public function testToStringFunction()
    {
        $name = 'Name';
        $this->assertEquals($name, (string) $this->company->setName($name));
    }

    protected function getContent()
    {
        return $this->company;
    }
}
