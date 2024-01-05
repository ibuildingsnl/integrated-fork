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

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Phonenumber;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Relation;
use PHPUnit\Framework\Assert;

trait RelationTestTrait
{
    /**
     * Relation should extend Relation.
     */
    public function testInstanceOfRelation()
    {
        Assert::assertInstanceOf(Relation::class, $this->getContent());
    }

    /**
     * Test get- and setAccountnumber function.
     */
    public function testGetAndSetAccountnumberFunction()
    {
        $content = $this->getContent();
        $content->setAccountnumber($accountnumber = 'accountnumber');

        Assert::assertEquals($accountnumber, $content->getAccountnumber());
    }

    /**
     * Test get- and setDescription function.
     */
    public function testGetAndSetDescriptionFunction()
    {
        $content = $this->getContent();
        $content->setDescription($description = 'accountnumber');

        Assert::assertEquals($description, $content->getDescription());
    }

    /**
     * Test get- and setPhonenumbers function.
     */
    public function testGetAndSetPhonenumbersFunction()
    {
        $content = $this->getContent();
        $content->setPhonenumbers($phonenumbers = [new Phonenumber('0123456789'), new Phonenumber('9876543210')]);

        Assert::assertSame($phonenumbers, $content->getPhonenumbers());
    }

    /**
     * Test addPhonenumber function.
     */
    public function testAddPhonenumberFunction()
    {
        $content = $this->getContent();
        $content->addPhonenumber('work', '0123456789');

        Assert::assertCount(1, $content->getPhonenumbers());
    }

    /**
     * Test addPhonenumber function with duplicate phonenumber.
     */
    public function testAddPhonenumberFunctionWithDuplicatePhonenumber()
    {
        $content = $this->getContent();

        $content->addPhonenumber('work', '0123456789');
        $content->addPhonenumber('work', '9876543210');

        Assert::assertCount(2, $content->getPhonenumbers());
    }

    /**
     * Test removePhonenumber function with unknown phonenumber.
     */
    public function testRemovePhonenumberFunctionWithUnknownPhonenumber()
    {
        $content = $this->getContent();
        $content->addPhonenumber('work', '0123456789');

        Assert::assertCount(1, $content->getPhonenumbers());

        $content->removePhonenumber('unknown');

        Assert::assertCount(1, $content->getPhonenumbers());
    }

    /**
     * @return Relation
     */
    abstract protected function getContent();
}
