<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Channel;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\Common\Collections\Collection;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Contact;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Socials;
use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Security\PermissionTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Channel document.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 *
 * @MongoDBUnique(fields="id")
 */
class Channel implements ChannelInterface
{
    use PermissionTrait;

    /**
     * @var string
     */
    #[Slug(fields: ['name'], separator: '_')]
    protected $id;

    /**
     * @var string the name of the channel
     */
    #[Assert\NotBlank]
    protected $name;

    /**
     * @var StorageInterface
     */
    protected $logo;

    /**
     * @var string
     */
    protected $color;

    /**
     * @var string
     */
    protected $secondarycolor;

    /**
     * @var array
     */
    protected $domains;

    /**
     * @var string
     */
    protected $primaryDomain;

    /**
     * @var bool
     */
    protected $primaryDomainRedirect;

    /**
     * @var Contact[]|Collection
     */
    protected $contacts;

    /**
     * @var Socials[]|Collection
     */
    protected $socials;

    /**
     * @var string
     */
    protected $vat;

    /**
     * @var string
     */
    protected $companyId;

    /**
     * @var string
     */
    protected $analytics;

    /**
     * @var mixed[]
     */
    protected $options = [];

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var bool
     */
    protected $ipProtected = false;

    /**
     * @var Scope
     */
    protected $scopeInstance = null;

    /**
     * @var null
     */
    protected $scope = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return StorageInterface
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param StorageInterface $logo
     *
     * @return $this
     */
    public function setLogo(StorageInterface $logo = null)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get the Contacts of the document.
     *
     * @return Contact[]
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Set the Contacts of the document.
     *
     * @return $this
     */
    public function setContacts(Collection $contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * Add Contact to Contacts collection.
     *
     * @param Contact $contact
     *
     * @return $this
     */
    public function addContact(Contact $contact = null)
    {
        if ($contact !== null) {
            $this->contacts->add($contact);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function removeContact(Contact $contact)
    {
        return $this->contacts->removeElement($contact);
    }

    /**
     * Get the Socials of the document.
     *
     * @return Socials[]
     */
    public function getSocials()
    {
        return $this->socials;
    }

    /**
     * Set the Socials of the document.
     *
     * @return $this
     */
    public function setSocials(Collection $socials)
    {
        $this->socials = $socials;

        return $this;
    }

    /**
     * Add Socials to Socials collection.
     *
     * @param Socials $socials
     *
     * @return $this
     */
    public function addSocials(Socials $socials = null)
    {
        if ($socials !== null) {
            $this->socials->add($socials);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function removeSocials(Socials $socials)
    {
        return $this->socials->removeElement($socials);
    }

    /**
     * @return string
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param string $vat
     *
     * @return $this
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @param string $companyId
     *
     * @return $this
     */
    public function setCompanyID($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * @return string
     */
    public function getAnalytics()
    {
        return $this->analytics;
    }

    /**
     * @param string $analytics
     *
     * @return $this
     */
    public function setAnalytics($analytics)
    {
        $this->analytics = $analytics;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     *
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecondaryColor()
    {
        return $this->secondarycolor;
    }

    /**
     * @param string $secondarycolor
     *
     * @return $this
     */
    public function setSecondaryColor($secondarycolor)
    {
        $this->secondarycolor = $secondarycolor;

        return $this;
    }

    /**
     * @return $this
     */
    public function setDomains(array $domains)
    {
        $this->domains = $domains;

        return $this;
    }

    /**
     * @return array
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @return mixed[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Overrider all the option with a new set of values for this content type.
     *
     * @param string[] $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = [];

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getOption($name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return null;
    }

    /**
     * Set the value of the specified key.
     *
     * @param string     $name
     * @param mixed|null $value
     *
     * @return $this
     */
    public function setOption($name, $value = null)
    {
        if ($value === null) {
            unset($this->options[$name]);
        } else {
            $this->options[$name] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    /**
     * Get the createdAt of the channel.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the createdAt of the channel.
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryDomain()
    {
        return $this->primaryDomain;
    }

    /**
     * @param string $primaryDomain
     */
    public function setPrimaryDomain($primaryDomain)
    {
        $this->primaryDomain = $primaryDomain;
    }

    /**
     * @return bool
     */
    public function getPrimaryDomainRedirect()
    {
        return $this->primaryDomainRedirect;
    }

    /**
     * @param bool $primaryDomainRedirect
     */
    public function setPrimaryDomainRedirect($primaryDomainRedirect)
    {
        $this->primaryDomainRedirect = $primaryDomainRedirect;
    }

    public function defaultPrimaryDomain()
    {
        if (!$this->primaryDomain) {
            $this->primaryDomain = reset($this->domains);
        }
    }

    public function isIpProtected(): bool
    {
        return (bool) $this->ipProtected;
    }

    /**
     * @return $this
     */
    public function setIpProtected(bool $protected)
    {
        $this->ipProtected = $protected ? true : null;

        return $this;
    }

    /**
     * @return Scope
     */
    public function getScope()
    {
        return $this->scopeInstance;
    }

    /**
     * @return $this
     */
    public function setScope(Scope $scope = null)
    {
        $this->scopeInstance = $scope;
        $this->scope = $scope ? $scope->getId() : null;

        return $this;
    }
}
