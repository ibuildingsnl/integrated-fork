# Integrated Storage Bundle

## Purpose
Provides file storage abstraction and file-serving routes for Integrated.

Core responsibilities:
- map content file references to configured filesystems
- resolve public file URLs
- support filesystem migration and cleanup operations
- expose file endpoints (`/storage/{id}.{ext}`)

## Install
In this repository the bundle is already wired through `integrated/integrated`.

For host applications:
- ensure bundle registration in `config/bundles.php`
- configure `knp_gaufrette` filesystems
- configure matching `integrated_storage` resolver entries
- import bundle routing (usually through aggregate routes)

Legacy host-app setup (pre-Flex/AppKernel style):

```php
// app/AppKernel.php
new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
new Integrated\Bundle\StorageBundle\IntegratedStorageBundle(),
```

```yaml
# app/routing.yml
integrated_storage:
  resource: "@IntegratedStorageBundle/Resources/config/routing/storage.xml"
  prefix: "/"
```

## Config
Main config node: `integrated_storage`.

Minimal example:

```yaml
# config/packages/knp_gaufrette.yaml
knp_gaufrette:
  adapters:
    documents_local:
      local:
        directory: '%kernel.project_dir%/public/uploads/documents'
  filesystems:
    documents_local:
      adapter: documents_local

# config/packages/integrated_storage.yaml
integrated_storage:
  identifier_class: Integrated\Bundle\StorageBundle\Storage\Identifier\FileIdentifier
  resolver:
    documents_local:
      public: /uploads/documents
      resolver_class: Integrated\Bundle\StorageBundle\Storage\Resolver\LocalResolver
  decision_map:
    Integrated\Bundle\ContentBundle\Document\Content\File:
      - documents_local
```

Notes:
- `resolver` is required and must contain at least one entry.
- `decision_map` is optional and controls preferred filesystems per class.

Compatibility details kept from earlier README:
- Storage places files on known filesystems when no decision mapping exists.
- Filesystem order in config determines primary path behavior.
- If a filesystem has no resolver, implement custom access logic for protected files.

### Decision Map
Use `decision_map` to force specific classes to specific filesystems (for example avoid public storage for sensitive types).

Important:
- redistribution commands (`storage:filesystem:add/remove`) do not enforce decision map restrictions retroactively; they operate on chosen filesystem targets.

### Identifier
Default identifier class:
- `Integrated\Bundle\StorageBundle\Storage\Identifier\FileIdentifier`

You can replace with a custom `Integrated\Common\Storage\Identifier\IdentifierInterface` implementation.

### Resolver
Resolver maps filesystem keys to browser-facing locations.

Default resolver class:
- `Integrated\Bundle\StorageBundle\Storage\Resolver\LocalResolver`

You can provide a custom resolver class per filesystem when public path logic is non-standard.

### Protecting Files
For non-public filesystems, serve content via controller/application logic instead of direct public path.

Example sketch:

```php
// File is an Integrated\Common\Content\Document\FileInterface object
$response = new Response();
$response->setContent($file->getContent());
$response->setHeaders($file->getMetadata()->getHeaders());
```

### Data Fixtures
Fixture helpers exist for generating storage-backed media in MongoDB fixtures, including:
- file helpers
- image helpers
- video helpers

When used, fixtures should write through storage manager abstractions so metadata/filesystem placement stays valid.

## Commands
- `php bin/console storage:migrate <path> [--delete] [--ignore-duplicates] [--find-empty]`
- `php bin/console storage:filesystem:list`
- `php bin/console storage:filesystem:add <filesystem>`
- `php bin/console storage:filesystem:remove <filesystem>`
- `php bin/console storage:filesystem:clean <filesystem> <directory>`

Command intent:
- `storage:migrate`: migrate old notation into current storage notation.
- `storage:filesystem:add/remove`: redistribute files to/from specific filesystem entries.
- `storage:filesystem:clean`: move unused files for cleanup review.

## Cron And Workers
No mandatory worker loop for this bundle.

Optional maintenance cadence:

```cron
# example monthly storage cleanup review preparation
0 3 1 * * cd /path/to/app && php bin/console storage:filesystem:clean documents_local /tmp/storage-clean --env=prod -q
```

## Verification
- Check configured filesystems:
  - `php bin/console storage:filesystem:list --env=prod`
- Validate route response:
  - `GET /storage/{id}.{ext}`
- Test migration on controlled dataset before bulk runs.

## Troubleshooting
- `filesystem ... does not exist`: verify key exists in `knp_gaufrette.filesystems`.
- Broken public URLs: verify `integrated_storage.resolver.<name>.public`.
- Duplicate file collision during migrate: retry with `--ignore-duplicates` only after validating source data.
- Large migration runs timing out: run in controlled batches and monitor memory/IO.

## License
MIT. See `LICENSE`.
