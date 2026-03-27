<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Document\Page;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\SeoMeta;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDBUnique(
 *     fields={"channel", "path"},
 *     message="This URL is already in use on this channel"
 * )
 */
class Page extends AbstractPage
{
    /**
     * @var string
     */
    #[Assert\NotBlank]
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var bool
     */
    protected $disabled = false;

    /**
     * @var bool
     */
    protected $locked = false;

    /**
     * @var string|null
     */
    protected $seoTitle;

    /**
     * @var string|null
     */
    protected $seoDescription;

    /**
     * @var string|null
     */
    protected $canonicalUrl;

    /**
     * @var bool|null
     */
    protected $paginationNoindexEnabled = null;

    /**
     * @var SeoMeta
     */
    protected $seoMetadata;

    /**
     * @var Image|null
     */
    protected $featuredImage;

    /**
     * @var string|null
     */
    protected $robotsDirective;

    /**
     * @var string|null
     */
    protected $twitterCard;

    public function __construct()
    {
        parent::__construct();

        $this->seoMetadata = new SeoMeta();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     *
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->disabled = (bool) $disabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     *
     * @return $this
     */
    public function setLocked($locked)
    {
        $this->locked = (bool) $locked;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @param string|null $seoTitle
     *
     * @return $this
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * @param string|null $seoDescription
     *
     * @return $this
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCanonicalUrl()
    {
        return $this->canonicalUrl;
    }

    /**
     * @param string|null $canonicalUrl
     *
     * @return $this
     */
    public function setCanonicalUrl($canonicalUrl)
    {
        $this->canonicalUrl = $canonicalUrl;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isPaginationNoindexEnabled()
    {
        return $this->paginationNoindexEnabled;
    }

    /**
     * @param bool|null $paginationNoindexEnabled
     *
     * @return $this
     */
    public function setPaginationNoindexEnabled($paginationNoindexEnabled)
    {
        $this->paginationNoindexEnabled = null === $paginationNoindexEnabled ? null : (bool) $paginationNoindexEnabled;

        return $this;
    }

    public function setSeoMetadata(SeoMeta $seoMetadata): void
    {
        $this->seoMetadata = $seoMetadata;
    }

    public function getSeoMetadata(): ?SeoMeta
    {
        return $this->seoMetadata;
    }

    public function getFeaturedImage(): ?Image
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(?Image $featuredImage): self
    {
        $this->featuredImage = $featuredImage;

        return $this;
    }

    public function getRobotsDirective(): ?string
    {
        return null === $this->robotsDirective ? null : (string) $this->robotsDirective;
    }

    public function setRobotsDirective($robotsDirective)
    {
        $this->robotsDirective = $robotsDirective;

        return $this;
    }

    public function getTwitterCard(): ?string
    {
        return null === $this->twitterCard ? null : (string) $this->twitterCard;
    }

    public function setTwitterCard($twitterCard)
    {
        $this->twitterCard = $twitterCard;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'page';
    }

    public function __clone()
    {
        $this->id = null;
    }
}
