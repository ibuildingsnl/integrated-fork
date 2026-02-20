<?php

namespace Integrated\Bundle\ContentBundle\Solr\Extension;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeExtensionInterface;

class PublicationsExtension implements TypeExtensionInterface
{
    private \DateTimeZone $timezone;

    public function __construct(
        private readonly PublicationRepositoryInterface $publications,
    ) {
        $this->timezone = new \DateTimeZone('UTC');
    }

    public function build(ContainerInterface $container, $data, array $options = []): void
    {
        if (!$data instanceof Content) {
            return;
        }

        foreach ($this->publications->forContent($data) as $publication) {
            $timeRange = $publication->getTime();
            $channel = $publication->getChannel();
            if (!$timeRange || !$channel) {
                continue;
            }

            $channelId = $channel->getId();

            $startDate = $timeRange->getStartDate();
            if ($startDate instanceof \DateTimeInterface) {
                $time = clone $startDate; // don't change original value

                $container->add(
                    'publication_start_'.$channelId.'_index_date',
                    $time->setTimezone($this->timezone)->format('Y-m-d\TG:i:s\Z'),
                );
            }

            $endDate = $timeRange->getEndDate();
            if ($endDate instanceof \DateTimeInterface) {
                $time = clone $endDate;

                $container->add(
                    'publication_end_'.$channelId.'_index_date',
                    $time->setTimezone($this->timezone)->format('Y-m-d\TG:i:s\Z'),
                );
            }
        }
    }

    public function getName(): string
    {
        return 'integrated.content';
    }
}
