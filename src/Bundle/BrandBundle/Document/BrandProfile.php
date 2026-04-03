<?php

namespace Integrated\Bundle\BrandBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Contact;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Social;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Ramsey\Uuid\Uuid;

class BrandProfile
{
    private string $id;
    public string $name = '';
    public string $color = '';
    public string $secondaryColor = '';
    public ?Image $logo = null;
    public ?Image $favicon = null;
    public ?string $vat = null;
    public ?string $companyId = null;
    public ?string $analytics = null;
    public ?string $analyticsPropertyId = null;
    private Collection $socials;
    private Collection $contacts;

    public function __construct(?string $id = null)
    {
        $this->id = $id ?: Uuid::uuid4()->toString();
        $this->socials = new ArrayCollection();
        $this->contacts = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLogo(): ?Image
    {
        return $this->logo;
    }

    public function setLogo(?Image $logo): void
    {
        $this->logo = $logo;
    }

    public function getFavicon(): ?Image
    {
        return $this->favicon;
    }

    public function setFavicon(?Image $favicon): void
    {
        $this->favicon = $favicon;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    public function getSecondaryColor(): ?string
    {
        return $this->secondaryColor;
    }

    public function setSecondaryColor(?string $secondaryColor): void
    {
        $this->secondaryColor = $secondaryColor;
    }

    /** @return Contact[] */
    public function getContacts(): array
    {
        return $this->contacts->toArray();
    }

    public function setContacts(Collection $contacts): void
    {
        $this->contacts = $contacts;
    }

    public function addContact(?Contact $contact = null): void
    {
        if ($contact !== null) {
            $this->contacts->add($contact);
        }
    }

    public function removeContact(Contact $contact): void
    {
        $this->contacts->removeElement($contact);
    }

    /** @return Social[] */
    public function getSocials(): array
    {
        return $this->socials->toArray();
    }

    public function addSocial(Social $social): void
    {
        $this->socials->add($social);
    }

    public function removeSocial(Social $social): void
    {
        $this->socials->removeElement($social);
    }
}
