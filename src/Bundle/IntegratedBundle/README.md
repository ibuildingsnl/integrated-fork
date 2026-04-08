# Integrated Bundle

## Purpose
Acts as the aggregate integration bundle that composes admin and website route imports and provides shared base controller utilities.

Core responsibilities:
- aggregate admin route imports from feature bundles
- aggregate website route imports for sitemap/storage/page/website flows
- provide admin entrypoint redirect (`/` -> dashboard or content index fallback)

## Install
In this repository this bundle is already part of `integrated/integrated`.

For host applications:
- ensure bundle registration in `config/bundles.php`
- import:
  - `@IntegratedIntegratedBundle/Resources/config/routing.yaml` for admin
  - `@IntegratedIntegratedBundle/Resources/config/routing.website.yaml` for website endpoints

Legacy host-app setup (pre-Flex/AppKernel style):

```php
// app/AppKernel.php
new Integrated\Bundle\IntegratedBundle\IntegratedIntegratedBundle()
```

## Config
No dedicated config tree for end-users.
Behavior is composition-driven through route import files:
- `Resources/config/routing.yaml` (admin aggregate)
- `Resources/config/routing.website.yaml` (website aggregate)

## Commands
This bundle does not register standalone console commands.
Use commands from the imported feature bundles (Solr, Workflow, Content, Storage, User, etc.).

## Cron And Workers
No direct cron/worker processes for this bundle.
Schedule workers based on the underlying feature bundles.

## Verification
- Admin root redirects correctly:
  - to `integrated_dashboard_index` when dashboard bundle/route is available
  - otherwise to `integrated_content_content_index`
- Admin and website aggregate routes resolve after cache warmup.

## Troubleshooting
- Missing admin sections: verify corresponding feature bundle routes are imported and bundle is enabled.
- Root redirect unexpected: verify dashboard route availability and route names.
- Route collisions: check host app custom route order against aggregate imports.

## License
MIT. See `LICENSE`.
