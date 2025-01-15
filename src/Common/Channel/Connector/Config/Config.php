<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Channel\Connector\Config;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class Config implements ConfigInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $adaptor;

    /**
     * @var OptionsInterface
     */
    private $options;

    /**
     * @var \DateTime
     */
    private $publicationStartDate;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $adaptor
     */
    public function __construct($name, $adaptor, OptionsInterface $options, ?\DateTime $publicationStartDate)
    {
        $this->name = $name;
        $this->adaptor = $adaptor;
        $this->options = $options;
        $this->publicationStartDate = $publicationStartDate;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAdapter()
    {
        return $this->adaptor;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getPublicationStartDate(): ?\DateTime
    {
        return $this->publicationStartDate;
    }
}
