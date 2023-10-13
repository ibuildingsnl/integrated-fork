<?php

namespace Integrated\Bundle\ContentBundle\Document\Content\Embedded;

class SeoMeta
{
    protected ?string $metaTitle = '';

    protected ?string $metaDescription = '';

    protected ?string $focusKeyphrase = '';

    protected ?string $seoScore = '';

    protected ?string $readabilityScore = '';

    public function getFocuskeyphrase(): ?string
    {
        return $this->focusKeyphrase;
    }

    public function setFocuskeyphrase(?string $focusKeyphrase): void
    {
        $this->focusKeyphrase = $focusKeyphrase;
    }

    public function getMetatitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetatitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getMetadescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetadescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getSeoScore(): ?string
    {
        return $this->seoScore;
    }

    public function setSeoScore(?string $seoScore): void
    {
        $this->seoScore = $seoScore;
    }

    public function getReadabilityScore(): ?string
    {
        return $this->readabilityScore;
    }

    public function setReadabilityScore(?string $readabilityScore): void
    {
        $this->readabilityScore = $readabilityScore;
    }
}
