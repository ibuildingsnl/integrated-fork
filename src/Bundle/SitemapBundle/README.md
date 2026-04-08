# IntegratedSitemapBundle #
This bundle provides the frontend of sitemaps

## Operations Runbook

### Purpose
- Public sitemap endpoints for content/pages/news
- Robots endpoint generation
- Channel-aware sitemap responses with cache headers and cache-pool storage

### Commands
This bundle does not expose standalone console commands.

### Key Routes
- `/sitemap.xml`
- `/sitemap{page}.xml`
- `/sitemap-pages-{page}.xml`
- `/sitemap-{type}-{page}.xml`
- `/news-sitemap.xml`
- `/news-sitemap{page}.xml`
- `/robots.txt`

### Cache Behavior
- Sitemap responses are cached via app cache pool.
- Controllers set explicit HTTP cache headers (`max-age`, `s-maxage`, `stale-while-revalidate`).
- Cache version manager keys drive invalidation behavior per channel/type/news/pages.

### Cron And Workers
No direct worker command; freshness depends on related content/page update flows and cache version bumps.

### Verification
- Fetch sitemap endpoints for a channel context and verify HTTP 200 + XML content.
- Confirm paginated endpoints return expected sets.
- Confirm robots endpoint includes sitemap references as expected.

### Troubleshooting
- Empty sitemap: verify channel context and indexable content availability.
- Stale sitemap output: verify cache version updates and cache pool health.
- Missing type sitemap: verify requested type is indexable and published.

## Requirements ##
* See the require section in the composer.json

## Features ##
* Sitemap management

## Documentation ##
* [Integrated for Developers](http://integratedfordevelopers.com/ "Integrated for Developers")

## Installation ##
This bundle can be installed following these steps:

### Install using composer ###

    $ php composer.phar require integrated/sitemap-bundle:^1.0

### Enable the bundle ###

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Integrated\Bundle\SitemapBundle\IntegratedSitemapBundle()
            // ...
        );
    }

### Import the routing ###

    # app/config/routing.yml
    integrated_sitemap:
        resource: @IntegratedSitemapBundle/Resources/config/routing.xml

## License ##
This bundle is under the MIT license. See the complete license in the bundle:

    LICENSE

## Contributing ##
Pull requests are welcome. Please see our [CONTRIBUTING guide](http://integratedfordevelopers.com/contributing "CONTRIBUTING guide").

## About ##
This bundle is part of the Integrated project. You can read more about this project on the
[Integrated for Developers](http://integratedfordevelopers.com/ "Integrated for Developers") website.
