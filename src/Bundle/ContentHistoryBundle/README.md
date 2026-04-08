# IntegratedContentHistoryBundle #
This bundle provides content history

## Operations Runbook

### Purpose
- Persist and expose content history change sets
- Clean redundant/irrelevant history fields through maintenance command

### Commands
- `php bin/console integrated:content-history:clean [--clean=<ClassFQN:field>]...`

Command behavior:
- removes no-op array diffs from update history items
- optionally removes configured fields per class from history change sets
- deletes history entries that end up with empty change sets

### Cron And Workers
Optional periodic cleanup:

```cron
0 4 * * 0 cd /path/to/app && php bin/console integrated:content-history:clean --env=prod -q
```

Example targeted cleanup:

```bash
php bin/console integrated:content-history:clean --clean='App\\Document\\Example:largeField'
```

### Verification
- Run command in a lower environment and inspect resulting history documents.
- Confirm history UI still renders meaningful diffs after cleanup.
- Validate command progress completes without errors on larger datasets.

### Troubleshooting
- `Classname not specified` / `Field not specified`: ensure each `--clean` value uses `ClassFQN:field`.
- Unexpected field removal: run on staging snapshot first and scope clean-table narrowly.
- Long runtime on large history sets: schedule off-peak and monitor ODM/memory usage.

## Requirements ##
* See the require section in the composer.json

## Features ##
* Content history for IntegratedContentBundle

## Documentation ##
* [Integrated for Developers](http://integratedfordevelopers.com/ "Integrated for Developers")

## Installation ##
This bundle can be installed following these steps:

### Install using composer ###

    $ php composer.phar require integrated/content-history-bundle:~0.5

### Enable the bundle ###

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Integrated\Bundle\ContentHistoryBundle\IntegratedContentHistoryBundle()
            // ...
        );
    }

### Import the routing ###

    # app/config/routing.yml
    integrated_content_history:
        resource: @IntegratedContentHistoryBundle/Resources/config/routing.xml
        prefix: "/admin"

## License ##
This bundle is under the MIT license. See the complete license in the bundle:

    LICENSE

## Contributing ##
Pull requests are welcome. Please see our [CONTRIBUTING guide](http://integratedfordevelopers.com/contributing "CONTRIBUTING guide").

## About ##
This bundle is part of the Integrated project. You can read more about this project on the
[Integrated for Developers](http://integratedfordevelopers.com/ "Integrated for Developers") website.
