<?php

namespace Integrated\Bundle\ChannelBundle\Model;

interface ConnectorConfigInterface
{
    public function getName(): string;

    public function getForm(): string;
}
