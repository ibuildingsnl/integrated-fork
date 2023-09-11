<?php

namespace Integrated\Common\Solr;

interface TitleResolverInterface
{
    public function getTitle(string $id): string;
}
