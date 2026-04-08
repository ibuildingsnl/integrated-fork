# IntegratedThemeBundle #
Provides website theme support, which makes it easy to share themes between websites or use a multi-site in your application

## Operations Runbook

### Purpose
- Theme registration and fallback resolution
- Theme manager integration for Twig/template loading
- Scraper management in admin and `scraper:run` execution

### Commands
- `php bin/console scraper:run`

### Cron And Workers
No mandatory worker loop.
Optional scheduler when scraper data must be refreshed periodically:

```cron
0 * * * * cd /path/to/app && php bin/console scraper:run --env=prod -q
```

### Verification
- `php bin/console scraper:run --env=prod`
- Open `/admin/scraper` and verify scraper records/pages.
- Confirm selected connector themes resolve to expected Twig paths.

### Troubleshooting
- Theme not resolving: verify `integrated_theme.themes.<theme>.paths` contains valid paths.
- Fallback loop/invalid fallback: verify `fallback` chain references existing theme ids.
- Scraper run has no effect: verify configured scraper pages and persistence layer.

### Legacy Notes
The original installation/configuration sections below are kept intentionally for older host apps using AppKernel-era setup.

## Requirements ##
* See the require section in the composer.json

## Features ##
* Theme support

## Documentation ##
* [Integrated for developers website](http://www.integratedfordevelopers.com "Integrated for developers website")

## Installation ##
This bundle can be installed following these steps:

### Install using composer ###

    $ php composer.phar require integrated/theme-bundle:~0.3

### Enable the bundle ###

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Integrated\Bundle\ThemeBundle\IntegratedThemeBundle()
            // ...
        );
    }

### Configuration ###

    # app/config/config.yml
    integrated_theme:
        themes:
            mytheme1:
                paths: 
                    - @AppBundle/themes/mytheme1
                fallback: 
                    - default
            mytheme2:
                paths:
                    - @AppBundle/themes/mytheme2
                    - @OtherBundle/themes/mytheme2
                fallback: 
                    - mytheme1
                    - mytheme3

## License ##
This bundle is under the MIT license. See the complete license in the bundle:

    LICENSE

## Contributing ##
Pull requests are welcome. Please see our [CONTRIBUTING guide](http://www.integratedfordevelopers.com/contributing "CONTRIBUTING guide").

## About ##
This bundle is part of the Integrated project. You can read more about this project on the
[Integrated for developers](http://www.integratedfordevelopers.com "Integrated for developers") website.
