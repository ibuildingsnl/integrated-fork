<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Bulk\Action;

use Integrated\Common\Bulk\BulkActionInterface;

class CanonicalAction implements BulkActionInterface
{
    /**
     * @var string
     */
    private $handler;

    /**
     * @var string|null
     */
    private $source;

    /**
     * @var string|null
     */
    private $sourceUrl;

    public function __construct(string $handler)
    {
        $this->handler = $handler;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function setSourceUrl(?string $sourceUrl): self
    {
        $this->sourceUrl = $sourceUrl;

        return $this;
    }

    public function getOptions()
    {
        return [
            'source' => $this->getSource(),
            'sourceUrl' => $this->getSourceUrl(),
        ];
    }
}
