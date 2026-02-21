<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Common\Content\Document\Storage\Embedded\MetadataInterface;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 */
class Metadata implements MetadataInterface
{
    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var string
     */
    protected $credits;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $metadata = [];

    public function __construct($extension, $mimeType, ArrayCollection $headers, ArrayCollection $metadata)
    {
        $this->extension = $extension;
        $this->mimeType = $mimeType;
        $this->headers = $headers->toArray();
        $this->metadata = $metadata->toArray();
    }

    public function storageData()
    {
        return new ArrayCollection(
            array_merge_recursive(
                $this->metadata,
                [
                    'headers' => array_replace($this->headers, ['Content-Type' => $this->mimeType]),
                ],
                ['credits' => $this->getCredits()],
                ['description' => $this->getDescription()]
            )
        );
    }

    public function getExtension()
    {
        return $this->extension;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function getCredits(): ?string
    {
        return $this->credits;
    }

    public function setCredits(?string $credits): void
    {
        $this->credits = $credits;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getHeaders()
    {
        return new ArrayCollection($this->headers);
    }

    public function getMetadata()
    {
        return new ArrayCollection($this->metadata);
    }
}
