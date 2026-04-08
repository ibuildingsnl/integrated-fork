# IntegratedImageBundle #
Implements and overwrites Gregwar\ImageBundle to provide additional functionality

## Operations Runbook

### Purpose
- Image handling and conversion integration on top of Gregwar image stack
- Twig image extension wiring via `image.handling`
- Fallback image and cache-directory behavior control

### Config
Main config node: `gregwar_image`.

Common options:
- `cache_dir`
- `cache_dir_mode`
- `throw_exception`
- `fallback_image`
- `web_dir`

### Commands
This bundle does not expose standalone console commands.

### Cron And Workers
No dedicated cron worker.

### Verification
- Render image in Twig using integrated image extension.
- Confirm generated cache files are written to configured cache dir.
- Verify fallback image behavior when source image is missing.

### Troubleshooting
- `setDirectoryMode`/cache issues on lower dependency sets: align `gregwar/cache` compatibility with lockfile constraints.
- Incorrect image URLs: validate `gregwar_image.web_dir` and asset package config.
- Missing fallback behavior: verify `gregwar_image.fallback_image` and `throw_exception` settings.

### Legacy Notes
The original installation/routing sections below are kept for compatibility with older host app setups.

## Requirements ##
* See the require section in the composer.json

## Documentation ##
* [Integrated for developers website](http://www.integratedfordevelopers.com "Integrated for developers website")

## Installation ##
This bundle can be installed following these steps:

### Install using composer ###

    $ php composer.phar require integrated/image-bundle:~0.1

### Enable the bundle ###

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Integrated\Bundle\ImageBundle\IntegratedImageBundle()
            // ...
        );
    }

### Import the routing ###

    # app/config/routing.yml
    integrated_image:
        resource: @IntegratedImageBundle/Resources/config/routing.xml

## License ##
This bundle is under the MIT license. See the complete license in the bundle:

    LICENSE

## Contributing ##
Pull requests are welcome. Please see our [CONTRIBUTING guide](http://www.integratedfordevelopers.com/contributing "CONTRIBUTING guide").

## About ##
This bundle is part of the Integrated project. You can read more about this project on the
[Integrated for developers](http://www.integratedfordevelopers.com/ "Integrated for developers") website.
