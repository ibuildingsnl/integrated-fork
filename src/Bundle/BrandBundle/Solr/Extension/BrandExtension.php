<?php

namespace Integrated\Bundle\BrandBundle\Solr\Extension;

use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeExtensionInterface;

class BrandExtension implements TypeExtensionInterface
{
    public function __construct(
        private readonly BrandRepository $brands,
    ) {
    }

    public function build(ContainerInterface $container, $data, array $options = []): void
    {
        if (!$data instanceof Content) {
            return;
        }

        $none = true;
        foreach ($this->brands->all() as $brand) {
            if ($brand->hasPublished($data)) {
                $none = false;
                $container->add('facet_brands', $brand->getId());
            }
        }
        if ($none) {
            $container->add('facet_brands', 'None');
        }
    }

    public function getName(): string
    {
        return 'integrated.content';
    }
}
