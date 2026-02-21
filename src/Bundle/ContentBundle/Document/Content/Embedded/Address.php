<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Content\Embedded;

use Integrated\Common\Form\Mapping\Attributes as Type;

/**
 * Embedded document Address.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class Address
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $address1;

    /**
     * @var string
     */
    protected $address2;

    /**
     * @var string
     */
    protected $zipcode;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var Location
     */
    protected $location;

    /**
     * @var int
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\HiddenType', options: ['attr' => ['data-itemorder' => 'collection']])]
    protected $order = 0;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getTitle(): ?string
    {
        return $this->address1.', '.$this->getCity();
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function setAddress1($address1): void
    {
        $this->address1 = $address1;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2($address2): void
    {
        $this->address2 = $address2;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode($zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity($city): void
    {
        $this->city = $city;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState($state): void
    {
        $this->state = $state;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry($country): void
    {
        $this->country = $country;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder($order): void
    {
        $this->order = (int) $order;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location = null): void
    {
        $this->location = $location;
    }
}
