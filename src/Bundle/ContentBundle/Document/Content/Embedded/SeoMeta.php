<?php

namespace Integrated\Bundle\ContentBundle\Document\Content\Embedded;

class SeoMeta
{
    protected string $metaTitle;

    protected string $metaSlug;

    protected string $metaDescription;

    protected string $focusKeyphrase;

    public function getFocuskeyphrase(): string
    {
        return $this->focusKeyphrase;
    }

    public function setFocuskeyphrase(string $focusKeyphrase): void
    {
        $this->focusKeyphrase = $focusKeyphrase;
    }

    public function getMetatitle(): string
    {
        return $this->metaTitle;
    }

    public function setMetatitle(string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getMetaslug(): string
    {
        return $this->metaSlug;
    }

    public function setMetaslug(string $metaSlug): void
    {
        $this->metaSlug = $metaSlug;
    }

    public function getMetadescription(): string
    {
        return $this->metaDescription;
    }

    public function setMetadescription(string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

}
