<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Content\Relation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Address;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Phonenumber;
use Integrated\Bundle\ContentBundle\Form\Type\AddressType;
use Integrated\Bundle\ContentBundle\Form\Type\PhonenumberType;
use Integrated\Bundle\FormTypeBundle\Form\Type\EditorType;
use Integrated\Bundle\FormTypeBundle\Form\Type\SortableCollectionType;
use Integrated\Common\Content\RankableInterface;
use Integrated\Common\Content\RankTrait;
use Integrated\Common\Form\Mapping\Attributes as Type;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Class for Relations.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
abstract class Relation extends Content implements RankableInterface
{
    use RankTrait;

    /**
     * @var string
     */
    #[Type\Field(options: ['attr' => ['style' => 'sidebar', 'icon' => 'wallet']], location: 'sidebar')]
    protected $accountnumber;

    /**
     * @var string
     */
    #[Type\Field(type: EditorType::class, options: [
        'priority' => 970,
        'attr' => [
            'style' => 'editor',
            'state' => 'show',
            'class' => 'content-edit-form',
            'placeholder' => 'Your content starts here',
        ],
    ], location: 'editor')]
    protected $description;

    /**
     * @var Collection<Phonenumber>
     */
    #[Type\Field(type: SortableCollectionType::class, options: [
        'entry_type' => PhonenumberType::class,
        'allow_add' => true,
        'allow_delete' => true,
        'add_button_text' => 'Add Phonenumber',
        'attr' => ['style' => 'editor', 'state' => 'show'],
    ], location: 'editor')]
    protected $phonenumbers;

    #[Type\Field(type: EmailType::class, options: [
        'attr' => [
            'style' => 'editor',
            'state' => 'show',
        ],
    ], location: 'editor')]
    protected ?string $email = null;

    /**
     * @var Collection<Address>
     */
    #[Type\Field(type: SortableCollectionType::class, options: [
        'entry_type' => AddressType::class,
        'default_title' => 'New address',
        'allow_add' => true,
        'allow_delete' => true,
        'add_button_text' => 'Add Address',
        'attr' => ['style' => 'editor', 'state' => 'show'],
    ], location: 'editor')]
    protected $addresses;

    /**
     * @var string
     */
    #[Type\Field(type: TextareaType::class, options: [
        'priority' => 490,
        'attr' => [
            'style' => 'editor',
            'state' => 'show',
        ],
    ], location: 'editor')]
    protected $intro;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->phonenumbers = new ArrayCollection();
        $this->addresses = new ArrayCollection();
    }

    public function getAccountnumber(): ?string
    {
        return $this->accountnumber;
    }

    public function setAccountnumber(string $accountnumber): void
    {
        $this->accountnumber = $accountnumber;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription($description): void
    {
        $this->description = $description;
    }

    public function getPhonenumbers($type = null): array|Collection
    {
        if ($type !== null) {
            $result = [];

            foreach ($this->phonenumbers as $obj) {
                if (strcasecmp($type, $obj->getType()) === 0) {
                    $result[] = $obj;
                }
            }

            return $result;
        }

        return $this->phonenumbers->toArray();
    }

    /**
     * @param Phonenumber[] $phonenumbers
     */
    public function setPhonenumbers(iterable $phonenumbers): void
    {
        $this->phonenumbers = new ArrayCollection();

        foreach ($phonenumbers as $phonenumber) {
            if ($this->isEmptyPhonenumber($phonenumber)) {
                continue;
            }

            $this->addPhonenumber($phonenumber);
        }
    }

    public function addPhonenumber(string|Phonenumber $phonenumber, ?string $type = null): void
    {
        if ($phonenumber === null || $this->isEmptyPhonenumber($phonenumber)) {
            return;
        }

        if ($phonenumber instanceof Phonenumber) {
            $obj = $phonenumber;
        } else {
            $obj = new Phonenumber($phonenumber, $type);
        }

        $this->phonenumbers->add($obj);
    }

    public function removePhonenumber(string|Phonenumber $phonenumber): bool
    {
        // @todo (INTEGRATED-452)
        if ($phonenumber instanceof Phonenumber) {
            return $this->phonenumbers->remove($phonenumber);
        }

        $return = false;

        foreach ($this->phonenumbers as $obj) {
            if (strcasecmp($phonenumber, $obj->getNumber()) === 0) {
                $return = $this->phonenumbers->remove($obj);
            }
        }

        return $return;
    }

    private function isEmptyPhonenumber(string|Phonenumber|null $phonenumber): bool
    {
        if ($phonenumber === null) {
            return true;
        }

        if ($phonenumber instanceof Phonenumber) {
            return '' === trim($phonenumber->getNumber());
        }

        return '' === trim($phonenumber);
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getAddresses(): array|Collection
    {
        return $this->addresses->toArray();
    }

    /**
     * @param Address[] $addresses
     */
    public function setAddresses(iterable $addresses): void
    {
        $this->addresses = new ArrayCollection();

        foreach ($addresses as $address) {
            $this->addAddress($address);
        }
    }

    public function addAddress(?Address $address = null): void
    {
        if ($address !== null) {
            $this->addresses->add($address);
        }
    }

    public function removeAddress(Address $address): bool
    {
        return $this->addresses->removeElement($address);
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setIntro(string $intro): void
    {
        $this->intro = $intro;
    }
}
