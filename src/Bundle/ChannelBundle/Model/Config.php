<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ChannelBundle\Model;

use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class Config implements ConfigInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $adapter;

    /**
     * @var array|Options
     */
    private $options;

    /**
     * @var string[]
     */
    private $channels = [];

    /**
     * @var \DateTime
     */
    private $publicationStartDate;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $updated;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
        $this->options = new Options();
        $this->publicationStartDate = new \DateTime();
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapter(): string
    {
        return $this->adapter;
    }

    /**
     * @return $this
     */
    public function setAdapter(string $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @param string[]|ChannelInterface[] $channels
     *
     * @return $this
     */
    public function setChannels($channels)
    {
        $this->channels = [];

        foreach ($channels as $channel) {
            $this->addChannel($channel);
        }

        return $this;
    }

    /**
     * @param string|ChannelInterface $channel
     *
     * @return $this
     */
    public function addChannel($channel)
    {
        if ($channel instanceof ChannelInterface) {
            $channel = $channel->getId();
        }

        $channel = (string) $channel;

        if (false === array_search($channel, $this->channels)) {
            $this->channels[] = $channel;
        }

        return $this;
    }

    /**
     * @param string|ChannelInterface $channel
     *
     * @return bool
     */
    public function hasChannel($channel)
    {
        if ($channel instanceof ChannelInterface) {
            $channel = $channel->getId();
        }

        $channel = (string) $channel;

        return false === array_search($channel, $this->channels) ? false : true;
    }

    /**
     * @param string|ChannelInterface $channel
     *
     * @return $this
     */
    public function removeChannel($channel)
    {
        if ($channel instanceof ChannelInterface) {
            $channel = $channel->getId();
        }

        $channel = (string) $channel;

        if (false !== ($key = array_search($channel, $this->channels))) {
            unset($this->channels[$key]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): OptionsInterface
    {
        if (!$this->options instanceof Options) {
            $this->options = \is_array($this->options) ? new Options($this->options) : new Options();
        }

        return $this->options;
    }

    /**
     * @param OptionsInterface $options
     *
     * @return $this
     */
    public function setOptions(OptionsInterface $options = null)
    {
        if ($options === null) {
            $options = new Options();
        } elseif (!$options instanceof Options) {
            $options = new Options($options->toArray());
        }

        $this->options = $options;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPublicationStartDate(): ?\DateTime
    {
        return $this->publicationStartDate;
    }

    /**
     * @param \DateTime $publicationStartDate
     */
    public function setPublicationStartDate(?\DateTime $publicationStartDate): void
    {
        $this->publicationStartDate = $publicationStartDate;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return $this
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return $this
     */
    public function setUpdated(\DateTime $updated)
    {
        $this->updated = $updated;

        return $this;
    }
}
