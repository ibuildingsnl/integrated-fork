# Integrated Installer Bundle

## Purpose
Orchestrates installation/bootstrap steps for Integrated host applications.

Main responsibilities:
- validate runtime prerequisites
- clear cache and install assets
- run SQL and MongoDB migration steps through installer command flow

## Install
In this repository the bundle is already part of `integrated/integrated`.

For host applications:
- ensure bundle registration in `config/bundles.php`
- run installer from host app root: `php bin/console integrated:install`

## Config
This bundle has no end-user config tree.

It wires migration infrastructure internally:
- custom SQL migration metadata table: `integrated_migration_versions`
- installer-prefixed Doctrine migration command aliases

Main entry points:
- `Command/IntegratedInstallCommand.php`
- `Command/IntegratedMongoDBMigrateCommand.php`
- `DependencyInjection/IntegratedInstallerExtension.php`
- `Resources/config/command.xml`

## Commands
- `php bin/console integrated:install [--step=<step>]...`
- `php bin/console integrated:install:mongodb:migrate` (hidden helper command)
- `php bin/console integrated:install:database:migrate` (installer-prefixed Doctrine migration command)

Supported `integrated:install --step` values:
- `tests`
- `cache`
- `assets`
- `migrations`

If no step is provided, all steps are executed in sequence.

## Cron And Workers
No recurring worker is required for this bundle.
Run installer commands during deploy/bootstrap flows, not as periodic cron jobs.

## Verification
- Full install check: `php bin/console integrated:install --env=prod`
- Migration-only check: `php bin/console integrated:install --step=migrations --env=prod`
- Confirm command exits non-zero on failed sub-steps in CI

## Troubleshooting
- `Could not open input file: /bin/console`: run command from project root and ensure relative path `bin/console` exists.
- Asset install fails during deploy: verify all package config options are valid before `assets:install`.
- Solr test step fails: verify Solr endpoint reachability and credentials used by `integrated_solr`.
- Mongo migrate step fails: verify MongoDB ODM connection and migration classes are available.

## License
MIT. See `LICENSE`.
