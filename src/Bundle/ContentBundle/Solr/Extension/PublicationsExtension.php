<?php

namespace Integrated\Bundle\ContentBundle\Solr\Extension;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeExtensionInterface;

class PublicationsExtension implements TypeExtensionInterface
{
    public function __construct(
        private readonly PublicationRepositoryInterface $publications,
    ) {
    }

    public function build(ContainerInterface $container, $data, array $options = []): void
    {
        if (!$data instanceof Content) {
            return;
        }
        foreach ($container->toArray() as $key => $value) {
            if (str_starts_with($key, 'publication_')) {
                $container->remove($key);
            }
        }
        foreach ($this->publications->forContent($data) as $publication) {
            $container->add(
                'publication_start_'.$publication->getChannel()->getId(),
                $publication->getTime()->getStartDate()->format('Y-m-d\TH:i:s\Z'),
            );
            $container->add(
                'publication_end_'.$publication->getChannel()->getId(),
                $publication->getTime()->getEndDate()->format('Y-m-d\TH:i:s\Z'),
            );
        }
    }

    public function getName(): string
    {
        return 'integrated.content';
    }
}
