<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Locks\Provider\DBAL;

use Integrated\Common\Locks\RequestInterface;
use Integrated\Common\Locks\ResourceInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class Request implements RequestInterface
{
    /**
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * @var ResourceInterface|null
     */
    protected $owner;

    /**
     * @var int|null
     */
    protected $timeout;

    /**
     * @param null $timeout
     */
    public function __construct(ResourceInterface $resource, ?ResourceInterface $owner = null, $timeout = null)
    {
        $this->resource = $resource;
        $this->owner = $owner;
        $this->timeout = $timeout;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Get the string representation of the request.
     *
     * @return string
     */
    public function __toString()
    {
        return \sprintf(
            "Resource: %s\nResourceOwner: %s\ntimeout: %s",
            method_exists($this->resource, '__toString') ? (string) $this->resource : $this->resource->getIdentifier(),
            $this->owner === null ? 'NULL' : $this->owner,
            $this->timeout === null ? 'NULL' : $this->timeout
        );
    }
}
