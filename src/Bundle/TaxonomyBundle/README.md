# Integrated Taxonomy Bundle

## Purpose
Provides taxonomy listing/editing flows and taxonomy usage-count APIs for Integrated content types.

Core responsibilities:
- taxonomy index/create/edit flow on `/taxonomy/{type}`
- usage count endpoint on `/taxonomy/{type}/usage-counts`
- taxonomy parent/channel inheritance listeners
- taxonomy repository/indexer/lister/viewer services

## Install
In this repository the bundle is already wired through `integrated/integrated`.

For host applications:
- ensure bundle registration in `config/bundles.php`
- import routing via aggregate routes or directly from `@IntegratedTaxonomyBundle/Resources/config/routing.yaml`

Legacy host-app setup (pre-Flex/AppKernel style):

```php
// app/AppKernel.php
new Integrated\Bundle\TaxonomyBundle\IntegratedTaxonomyBundle()
```

## Config
This bundle does not expose a dedicated config tree.
Core services are declared in `Resources/config/services.xml`.

Primary routes:
- `GET|POST /taxonomy/{type}` -> index/form flow
- `GET /taxonomy/{type}/usage-counts` -> JSON usage counts for given taxonomy ids

## Commands
This bundle does not register standalone console commands.

## Cron And Workers
No mandatory cron worker for taxonomy itself.
Related indexing side effects follow Content/Solr/Workflow schedules.

## Verification
- Open `/taxonomy/<type>` and verify list/form render for authorized users.
- Call usage endpoint with ids query:
  - `/taxonomy/<type>/usage-counts?ids[]=<id1>&ids[]=<id2>`
- Confirm response shape:
  - `{ "counts": { ... } }`

## Troubleshooting
- Access denied on taxonomy pages: verify permissions for taxonomy create/edit.
- Empty usage counts: verify taxonomy ids passed in `ids[]` and content type mapping.
- Slow taxonomy index page: profile taxonomy repository/indexer usage and related permission checks.

## License
MIT. See `LICENSE`.
