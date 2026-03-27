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
