# Integrated Brand Bundle

## Purpose
Manages brands, brand-channel links, and connector configuration within the Integrated admin.

Core responsibilities:
- brand CRUD and channel link management under `/brand/*`
- connector configuration per brand/channel link
- Solr facet integration and cache invalidation around brand data
- background cleanup utility for invalid brand-channel links

## Install
In this repository the bundle is already wired through `integrated/integrated`.

For host applications:
- ensure bundle registration in `config/bundles.php`
- import bundle routing (commonly through `IntegratedIntegratedBundle` aggregate admin routes)

Legacy host-app setup (pre-Flex/AppKernel style):

```php
// app/AppKernel.php
new Integrated\Bundle\BrandBundle\IntegratedBrandBundle()
```

## Config
This bundle does not expose a dedicated config tree.
Service wiring is defined in `Resources/config/services.xml`.

Primary admin route import:
- `@IntegratedBrandBundle/Resources/config/routing/brand.yaml` with prefix `/brand`

## Commands
- `php bin/console integrated:brand:cleanup-channel-links [--channel-id=<id>] [--dry-run]`

Command behavior:
- removes null channel links and optionally links for a specific channel id
- prints per-link report lines
- supports dry-run mode for safe preview

## Cron And Workers
No mandatory recurring worker.

Optional periodic cleanup:

```cron
0 3 * * 0 cd /path/to/app && php bin/console integrated:brand:cleanup-channel-links --dry-run --env=prod -q
```

Use non-dry run only after reviewing output.

## Verification
- Dry run:
  - `php bin/console integrated:brand:cleanup-channel-links --dry-run --env=prod`
- Channel-specific run:
  - `php bin/console integrated:brand:cleanup-channel-links --channel-id=<id> --env=prod`
- Validate brand edit/connectors still render and save correctly in admin.

## Troubleshooting
- Unexpected link removals: always run with `--dry-run` first.
- Missing command in host app: verify bundle is enabled and cache is rebuilt.
- Connector manage route issues: verify channel link ids and connector assignment state.

## License
MIT. See `LICENSE`.
