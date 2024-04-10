<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Document\Content\Embedded;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Address;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class AddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Address
     */
    private $address;

    protected function setUp(): void
    {
        $this->address = new Address();
    }

    public function testGetAndSetTypeFunction()
    {
        $type = 'type';

        $this->address->setType($type);
        $this->assertEquals($type, $this->address->getType());
    }

    public function testGetAndSetAddress1Function()
    {
        $address1 = 'address1';

        $this->address->setAddress1($address1);
        $this->assertEquals($address1, $this->address->getAddress1());
    }

    public function testGetAndSetAddress2Function()
    {
        $address2 = 'address2';
        $this->address->setAddress2($address2);
        $this->assertEquals($address2, $this->address->getAddress2());
    }

    public function testGetAndSetZipcodeFunction()
    {
        $zipcode = 'zipcode';
        $this->address->setZipcode($zipcode);
        $this->assertEquals($zipcode, $this->address->getZipcode());
    }

    public function testGetAndSetCityFunction()
    {
        $city = 'city';
        $this->address->setCity($city);
        $this->assertEquals($city, $this->address->getCity());
    }

    public function testGetAndSetStateFunction()
    {
        $state = 'state';
        $this->address->setState($state);
        $this->assertEquals($state, $this->address->getState());
    }

    public function testGetAndSetCountryFunction()
    {
        $country = 'city';
        $this->address->setCountry($country);
        $this->assertEquals($country, $this->address->getCountry());
    }
}
