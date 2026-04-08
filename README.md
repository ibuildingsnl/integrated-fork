# Integrated Core (`integrated/integrated`)

Integrated Core is the central package that provides the core bundles, shared
domain/runtime code, and admin/website building blocks for Integrated CMS.

This is a package repository (library), not a standalone Symfony application.
You usually run Symfony console commands from the host app where this package
is installed.

## Table Of Contents

1. Overview
2. Requirements
3. Installation
4. Repository Structure
5. Full Command Reference
6. Symfony Console Commands (provided by this package)
7. CI Parity
8. Development Notes
9. License

## Overview

This package contains:

- Symfony bundles under `src/Bundle`
- Shared cross-bundle runtime code under `src/Common`
- Doctrine/MongoDB integrations under `src/Doctrine` and `src/MongoDB`
- Front-end asset sources and build config for admin UI

### Bundles Included

- `AssetBundle`
- `BlockBundle`
- `BrandBundle`
- `ChannelBundle`
- `CommentBundle`
- `ContentBundle`
- `ContentHistoryBundle`
- `FormTypeBundle`
- `ImageBundle`
- `InstallerBundle`
- `IntegratedBundle`
- `LockingBundle`
- `MenuBundle`
- `PageBundle`
- `SitemapBundle`
- `SlugBundle`
- `SolrBundle`
- `StorageBundle`
- `TaxonomyBundle`
- `ThemeBundle`
- `UserBundle`
- `WebsiteBundle`
- `WorkflowBundle`

## Requirements

- PHP `>=8.1`
- Composer
- Node.js + npm or yarn (for frontend build)
- PHP extensions:
  - `ext-gd`
  - `ext-intl`
  - `ext-json`

## Installation

### In a host project

```bash
composer require integrated/integrated
```

### For local development of this package

Run from this repository root (`vendor/integrated/integrated` in your current
workspace):

```bash
composer install
npm install
```

## Repository Structure

### Namespace roots

- `src/Bundle/*` -> `Integrated\Bundle\*`
- `src/Common/*` -> `Integrated\Common\*`
- `src/Doctrine/*` -> `Integrated\Doctrine\*`
- `src/MongoDB/*` -> `Integrated\MongoDB\*`

### Frontend build output

- Output path: `src/Bundle/IntegratedBundle/Resources/public`
- Public path: `/bundles/integratedintegrated`
- Config file: `webpack.config.js`

## Full Command Reference

Run all commands below from repository root: `vendor/integrated/integrated`.

### Dependency Management

```bash
composer install
composer update -n --ansi
composer update -n --ansi --prefer-lowest
composer validate
```

### Frontend / Assets

```bash
npm install
npm run dev
npm run watch
npm run build
npm run dev-server
```

### PHPUnit

```bash
php vendor/bin/phpunit
php vendor/bin/phpunit -c phpunit.xml.dist
php vendor/bin/phpunit --filter testName
php vendor/bin/phpunit src/Common/Queue/Tests
php vendor/bin/phpunit src/Bundle/ContentBundle/Tests
php vendor/bin/phpunit src/Bundle/UserBundle/Tests
```

### Bundle-Specific PHPUnit Config Files

The following bundle test configs exist:

- `src/Bundle/AssetBundle/phpunit.xml.dist`
- `src/Bundle/BlockBundle/phpunit.xml.dist`
- `src/Bundle/ChannelBundle/phpunit.xml.dist`
- `src/Bundle/CommentBundle/phpunit.xml.dist`
- `src/Bundle/ContentBundle/phpunit.xml.dist`
- `src/Bundle/ContentHistoryBundle/phpunit.xml.dist`
- `src/Bundle/FormTypeBundle/phpunit.xml.dist`
- `src/Bundle/ImageBundle/phpunit.xml.dist`
- `src/Bundle/LockingBundle/phpunit.xml.dist`
- `src/Bundle/MenuBundle/phpunit.xml.dist`
- `src/Bundle/PageBundle/phpunit.xml.dist`
- `src/Bundle/SitemapBundle/phpunit.xml.dist`
- `src/Bundle/SlugBundle/phpunit.xml.dist`
- `src/Bundle/SolrBundle/phpunit.xml.dist`
- `src/Bundle/StorageBundle/phpunit.xml.dist`
- `src/Bundle/ThemeBundle/phpunit.xml.dist`
- `src/Bundle/UserBundle/phpunit.xml.dist`
- `src/Bundle/WebsiteBundle/phpunit.xml.dist`
- `src/Bundle/WorkflowBundle/phpunit.xml.dist`

### PHP CS Fixer

```bash
php vendor/bin/php-cs-fixer fix --verbose --show-progress=none --dry-run
php vendor/bin/php-cs-fixer fix --verbose --show-progress=none
php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --path-mode=intersection --dry-run src/Common
```

### PHPStan

```bash
php vendor/bin/phpstan analyse --no-progress
php vendor/bin/phpstan analyse --no-progress --configuration=phpstan.lowest.neon
php vendor/bin/phpstan analyse --no-progress src/Common
```

Notes:

- `phpstan.neon` includes `phpstan-baseline.neon`.
- Lowest dependency runs use `phpstan.lowest.neon`.

### Other Available Tooling Binaries

```bash
php vendor/bin/rector
php vendor/bin/yaml-lint
php vendor/bin/sql-formatter
php vendor/bin/php-parse
php vendor/bin/patch-type-declarations
php vendor/bin/var-dump-server
php vendor/bin/doctrine-migrations
```

## Symfony Console Commands (Provided By This Package)

These commands are registered when the bundle is loaded in a Symfony host app.
Run them from the host app root, typically:

```bash
php bin/console <command>
```

### Installer

- `integrated:install` - Run installer steps
- `integrated:install:mongodb:migrate` - Run MongoDB migration step

### Solr / Indexing

- `solr:indexer:queue` - Queue content for indexing
- `solr:indexer:run` - Execute indexer run
- `solr:worker:run` - Process queued Solr worker tasks
- `workflow:index` - Queue indexing for workflow content
- `workflow:worker:run` - Process workflow queue tasks

### Locking

- `locking:clear` - Remove all locks
- `locking:dbal:clean` - Remove expired locks

### Channel / Publication

- `channel:export` - Execute channel exporter run

### Storage

- `storage:migrate` - Migrate old file notation to configured storage
- `storage:filesystem:list` - List configured filesystems
- `storage:filesystem:add` - Add files into filesystem
- `storage:filesystem:remove` - Remove filesystem and copy files
- `storage:filesystem:clean` - Remove unused files

### User

- `user:create` - Create user
- `user:password:change` - Change user password

### Brand / Theme / History

- `integrated:brand:cleanup-channel-links` - Cleanup invalid brand-channel links
- `scraper:run` - Run theme scraper
- `integrated:content-history:clean` - Cleanup content history

### Discover Commands In Host App

```bash
php bin/console list
php bin/console list | grep -E 'integrated|solr|workflow|locking|storage|channel|user|scraper'
```

## Operations Cron Matrix (Typical)

Use as baseline and tune per workload:

```cron
# Solr indexing pipeline
* * * * * cd /path/to/app && php bin/console solr:indexer:run --full --env=prod -q
* * * * * cd /path/to/app && php bin/console solr:worker:run --tasks=1000 --env=prod -q

# Workflow queue processing
* * * * * cd /path/to/app && php bin/console workflow:worker:run --batch=10 --env=prod -q

# Channel export
*/5 * * * * cd /path/to/app && php bin/console channel:export --env=prod -q

# Lock cleanup
*/5 * * * * cd /path/to/app && php bin/console locking:dbal:clean --env=prod -q

# Draft cleanup
0 2 * * * cd /path/to/app && php bin/console integrated:content:draft:cleanup --env=prod -q
```

For newsletter cron and adapter behavior, see package-level READMEs:
- `../newsletter/README.md`
- `../sendinblue/README.md`

## Bundle Runbook Index

Operational bundle runbooks:
- `src/Bundle/SolrBundle/README.md`
- `src/Bundle/WorkflowBundle/README.md`
- `src/Bundle/LockingBundle/README.md`
- `src/Bundle/ChannelBundle/README.md`
- `src/Bundle/ContentBundle/README.md`
- `src/Bundle/StorageBundle/README.md`
- `src/Bundle/InstallerBundle/README.md`

## CI Parity

`jenkins.build` runs roughly:

1. Fresh dependency install (`composer update`)
2. `php-cs-fixer --dry-run`
3. `phpunit`
4. `phpstan`
5. Repeat with `--prefer-lowest`
6. `phpunit` + `phpstan --configuration=phpstan.lowest.neon`

Equivalent local sequence:

```bash
composer update -n --ansi
php vendor/bin/php-cs-fixer fix --verbose --show-progress=none --dry-run
php vendor/bin/phpunit
php vendor/bin/phpstan analyse --no-progress

composer update -n --ansi --prefer-lowest
php vendor/bin/phpunit
php vendor/bin/phpstan analyse --no-progress --configuration=phpstan.lowest.neon
```

## Development Notes

- Keep changes scoped to the relevant bundle or `src/Common` module.
- Add/adjust tests for behavior changes.
- Prefer explicit error handling in critical runtime paths (queueing, locking,
  indexing, flushing).
- Run targeted checks during development, then full checks before release.

## License

MIT. See [`LICENSE`](LICENSE).
