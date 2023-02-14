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
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Social;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Common\Content\Channel\ChannelInterface;
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
     * @var Image
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
     * @var Social[]|Collection
     */
    protected $social;

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
     * @return Image|null
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @return $this
     */
    public function setLogo(Image $logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return Contact[]
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @return $this
     */
    public function setContacts(Collection $contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
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
     * @return Social[]
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * @return $this
     */
    public function setSocial(Collection $social)
    {
        $this->social = $social;

        return $this;
    }

    /**
     * @param Social $social
     *
     * @return $this
     */
    public function addSocial(Social $social = null)
    {
        if ($social !== null) {
            $this->social->add($social);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function removeSocial(Social $social)
    {
        return $this->social->removeElement($social);
    }

    /**
     * @return string
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @return $this
     */
    public function setVat(string $vat)
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
     * @return $this
     */
    public function setCompanyID(string $companyId)
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
     * @return $this
     */
    public function setAnalytics(string $analytics)
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
     * @return $this
     */
    public function setColor(string $color)
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
     * @return $this
     */
    public function setSecondaryColor(string $secondarycolor)
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
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
