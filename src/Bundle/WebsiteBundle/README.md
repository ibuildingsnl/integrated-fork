# IntegratedWebsiteBundle #
This bundle provides a website front-end for content in Integrated and also the capabilities to edit pages and navigation.

## Operations Runbook

### Purpose
- Frontend rendering for Integrated content/page routes
- Website-level menu/grid/session/search-selection routes
- JSON and RSS endpoints for content/search selections
- Website connector registration for channel integrations

### Commands
This bundle does not expose standalone console commands.

### Key Routes
- JSON feed: `/content/json/{id}`
- RSS feed: `/content/rss/{id}`
- Related content block JSON: `/related_content_block/json`
- Additional website routes are imported via `Resources/config/routing.yaml`

### Cron And Workers
No direct cron/worker process for this bundle.

### Verification
- Resolve a page route on website frontend.
- Verify JSON endpoint response for a known selection id.
- Verify RSS endpoint response for a known selection id.

### Troubleshooting
- Website route missing: verify `routing.website.yaml` aggregate imports include Page/Website bundles.
- Connector behavior missing: verify `integrated_website.connector.website_adapter` registration.
- Production exception page mismatch: check `IntegratedWebsiteExtension::prepend()` behavior for non-dev environments.

## Requirements ##
* See the require section in the composer.json

## Features ##
* Provides a website front-end for content in Integrated
* Provides capabilities to edit pages
* Provides capabilities to edit navigation

## Documentation ##
* [Integrated for developers website](http://www.integratedfordevelopers.com "Integrated for developers website")

## Installation ##
This bundle can be installed following these steps:

### Install using composer ###

    $ php composer.phar require integrated/website-bundle:~0.3

### Enable the bundle ###

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Integrated\Bundle\WebsiteBundle\IntegratedWebsiteBundle()
            // ...
        );
    }

### Import the routing ###

    # app/config/routing.yml
    integrated_website:
        resource: @IntegratedWebsiteBundle/Resources/config/routing.xml

## License ##
This bundle is under the MIT license. See the complete license in the bundle:

    LICENSE

## Contributing ##
Pull requests are welcome. Please see our [CONTRIBUTING guide](http://www.integratedfordevelopers.com/contributing "CONTRIBUTING guide").

## About ##
This bundle is part of the Integrated project. You can read more about this project on the
[Integrated for developers](http://www.integratedfordevelopers.com "Integrated for developers") website.
