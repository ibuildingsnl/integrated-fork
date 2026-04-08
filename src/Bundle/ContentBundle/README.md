# Integrated Content Bundle

## Purpose
Core content domain bundle for Integrated CMS.

It provides:
- content, media, channel, relation and content-type admin routes
- content repositories, metadata and form integrations
- Solr integration hooks and serialization services
- editor locking/UI integration points and content lifecycle listeners

## Install
In this repository the bundle is already wired through `integrated/integrated`.

For host applications:
- ensure bundle registration in `config/bundles.php`
- import bundle routing (normally through `IntegratedIntegratedBundle` admin aggregate routes)

Legacy host-app setup (pre-Flex/AppKernel style):

```php
// app/AppKernel.php
new Integrated\Bundle\ContentBundle\IntegratedContentBundle()
```

```yaml
# app/config/routing.yml
integrated_content:
  resource: "@IntegratedContentBundle/Resources/config/routing.xml"
```

## Config
This bundle does not expose a dedicated `Configuration` tree.

Operational behavior is primarily service-driven (`Resources/config/*.xml`), including:
- routing imports under `/content`, `/contenttype`, `/channel`, `/media`, `/relation`, etc.
- repository and resolver wiring for content retrieval and publication behavior
- editor and channel integration listeners

Legacy note:
- older host apps that still use Assetic/SpBower can keep an `integrated_css` / `integrated_js` pipeline.
- for this repository and modern setups, Encore-based asset handling is the active path.

Legacy Assetic/SpBower snippet (kept for compatibility projects):

```yaml
# app/config/config.yml
sp_bower:
  bundles:
    IntegratedContentBundle: ~

assetic:
  filters:
    sass:
      bin: /usr/bin/sass
      apply_to: "\.scss$"
      style: compressed
  assets:
    integrated_css:
      inputs:
        - @IntegratedContentBundle/Resources/public/sass/main.scss
      filters:
        - sass
      output: css/main.css
    integrated_js:
      inputs:
        # Add your custom javascript files here
```

## Commands
- `php bin/console integrated:content:draft:cleanup [--draft-max-age-days=<n>] [--version-max-age-days=<n>] [--max-versions=<n>] [--dry-run]`
- `php bin/console integrated:content:channel:delete --channel-id=<id> [--delete-referenced]`

Command intent:
- `integrated:content:draft:cleanup` prunes stale draft documents and old draft versions.
- `integrated:content:channel:delete` performs channel deletion with reporting and optional removal of referenced documents.

## Cron And Workers
Recommended periodic maintenance:

```cron
# prune stale drafts and oversized draft version history
0 2 * * * cd /path/to/app && php bin/console integrated:content:draft:cleanup --env=prod -q
```

`integrated:content:channel:delete` is usually invoked operationally or by workflow queue handlers, not by fixed cron.

## Verification
- Dry-run draft cleanup:
  - `php bin/console integrated:content:draft:cleanup --dry-run --env=prod`
- Execute cleanup:
  - `php bin/console integrated:content:draft:cleanup --env=prod`
- Channel delete validation:
  - `php bin/console integrated:content:channel:delete --channel-id=<id> --env=prod`

## Troubleshooting
- Missing `--channel-id`: command returns failure by design.
- Channel no longer exists: command exits successfully and logs skip message.
- Large cleanup runtime: start with `--dry-run` and tune age/version options before write run.
- Unexpected UI behavior around content locks/queues: verify related Locking/Workflow/Solr workers are active.

## License
MIT. See `LICENSE`.
