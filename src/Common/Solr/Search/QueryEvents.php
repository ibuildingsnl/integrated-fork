<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Search;

use Integrated\Common\Solr\Search\Event\ConfigureOptionsEvent;
use Integrated\Common\Solr\Search\Event\CreateEvent;
use Integrated\Common\Solr\Search\Event\PostCreateEvent;
use Integrated\Common\Solr\Search\Event\PreCreateEvent;

class QueryEvents
{
    /**
     * This event will allow you to return a Query object which will then be returned by the
     * factory without any modifications.
     */
    public const PRE_CREATE = 'integrated.solr.search.pre_create_query';

    /**
     * This event will allow you to create a Query object that will be used by the factory, if
     * no query is returned then the factory will create one without any options set.
     */
    public const CREATE = 'integrated.solr.search.create_query';

    /**
     * This even will allow you to modify the query after its has build by the factory.
     */
    public const POST_CREATE = 'integrated.solr.search.post_create_query';

    /**
     * This event will allow you to add or change options.
     */
    public const CONFIGURE_OPTIONS = 'integrated.solr.search.configure_options';

    public const ALIASES = [
        PreCreateEvent::class => self::PRE_CREATE,
        CreateEvent::class => self::CREATE,
        PostCreateEvent::class => self::POST_CREATE,
        ConfigureOptionsEvent::class => self::CONFIGURE_OPTIONS,
    ];
}
