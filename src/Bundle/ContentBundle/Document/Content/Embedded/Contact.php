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
 * Embedded document Contact.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
#[Type\Document('Embedded')]
class Contact
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
     * @var string|null
     */
    protected $email;

    /**
     * @var string|null
     */
    protected $phonenumber;

    /**
     * @var string|null
     */
    protected $mobilenumber;

    /**
     * @var int
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\HiddenType', options: ['attr' => ['data-itemorder' => 'collection']])]
    protected $order;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTitle(): string
    {
        return $this->address1.', '.$this->getCity();
    }

    public function getAddress1(): string
    {
        return $this->address1;
    }

    public function setAddress1(string $address1): void
    {
        $this->address1 = $address1;
    }

    public function getAddress2(): string
    {
        return $this->address2;
    }

    public function setAddress2(string $address2): void
    {
        $this->address2 = $address2;
    }

    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @return $this
     */
    public function setOrder(int $order): static
    {
        $this->order = $order;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @param Location $location
     *
     * @return $this
     */
    public function setLocation(Location $location = null): static
    {
        $this->location = $location;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPhonenumber(): ?string
    {
        return $this->phonenumber;
    }

    public function setPhonenumber(?string $phonenumber): void
    {
        $this->phonenumber = $phonenumber;
    }

    public function getMobilenumber(): ?string
    {
        return $this->mobilenumber;
    }

    public function setMobilenumber(?string $mobilenumber): void
    {
        $this->mobilenumber = $mobilenumber;
    }
}
