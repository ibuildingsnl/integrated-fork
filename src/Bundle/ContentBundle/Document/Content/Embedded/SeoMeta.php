<?php

namespace Integrated\Bundle\ContentBundle\Document\Content\Embedded;

class SeoMeta
{
    /**
     * @var string
     */
    protected $metaTitle = '';

    /**
     * @var string
     */
    protected $metaDescription = '';

    /**
     * @var string
     */
    protected $focusKeyphrase = '';

    /**
     * @var string
     */
    protected $seoScore = '';

    /**
     * @var string
     */
    protected $readabilityScore = '';

    public function getFocuskeyphrase(): ?string
    {
        return $this->focusKeyphrase;
    }

    public function setFocuskeyphrase($focusKeyphrase): void
    {
        $this->focusKeyphrase = $focusKeyphrase;
    }

    public function getMetatitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetatitle($metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getMetadescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetadescription($metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getSeoScore(): ?string
    {
        return $this->seoScore;
    }

    public function setSeoScore($seoScore): void
    {
        $this->seoScore = $seoScore;
    }

    public function getReadabilityScore(): ?string
    {
        return $this->readabilityScore;
    }

    public function setReadabilityScore($readabilityScore): void
    {
        $this->readabilityScore = $readabilityScore;
    }
}
