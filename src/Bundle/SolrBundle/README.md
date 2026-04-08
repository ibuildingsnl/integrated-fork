# Integrated Solr Bundle

## Purpose
Provides Solr indexing and worker processing for Integrated content.

This bundle exposes:
- queueing commands to stage index jobs
- indexer commands to transform queue jobs into Solr documents
- worker commands to execute queued Solr tasks

## Install
In this repository the bundle is already wired through `integrated/integrated`.

For host applications:
- install package dependency that includes this bundle
- ensure bundle registration in Symfony (`config/bundles.php`)
- ensure routing aggregation from `IntegratedIntegratedBundle` is enabled

Legacy host-app setup (pre-Flex/AppKernel style):

```php
// app/AppKernel.php
new Integrated\Bundle\SolrBundle\IntegratedSolrBundle()
```

Historical note:
- older docs referenced `init:queue`; queue bootstrap is now handled through current queue/service wiring and installer flow.

## Config
Main config node: `integrated_solr`.

Example:

```yaml
# config/packages/integrated_solr.yaml
integrated_solr:
  timeout: 200
  endpoints:
    default:
      scheme: http
      host: localhost
      port: 8983
      path: ""
      core: integrated
      username: null
      password: null
```

Operational notes:
- the default queue provider is DBAL (`integrated_queue.dbal.provider`)
- DB table defaults are `queue` (queue provider) and queue names `solr-indexer` / `solr-worker`

## Commands
- `php bin/console solr:indexer:queue [id ...] [--full] [--delete] [--commit] [--ignore]`
- `php bin/console solr:indexer:run [processes] [--full] [--daemon] [--wait=<ms>] [--blocking]`
- `php bin/console solr:worker:run [--tasks=<n>]`

Quick command intent:
- `solr:indexer:queue`: enqueue (re)index, delete, or commit jobs
- `solr:indexer:run`: process indexer queue into Solr worker tasks
- `solr:worker:run`: execute Solr worker tasks against Solarium client

## Cron And Workers
Recommended production pattern:

```cron
# keep converting index queue messages into worker tasks
* * * * * cd /path/to/app && php bin/console solr:indexer:run --full --env=prod -q

# execute worker tasks (set --tasks to cap work per run)
* * * * * cd /path/to/app && php bin/console solr:worker:run --tasks=1000 --env=prod -q
```

Optional:
- use `solr:indexer:run <processes> --blocking` for parallel catch-up runs
- use `solr:indexer:queue --full` for a full reindex trigger

## Verification
- Queue content: `php bin/console solr:indexer:queue --full --env=prod`
- Process queue once: `php bin/console solr:indexer:run --env=prod`
- Execute worker once: `php bin/console solr:worker:run --env=prod`
- Confirm no queue growth after steady-state runs

## Troubleshooting
- Lock conflicts on `solr:indexer:run`/`solr:worker:run`: another process is active; avoid overlapping cron jobs.
- Queue keeps growing: verify both indexer and worker cron entries are active.
- Solr connection failures: verify `integrated_solr.endpoints.*` and timeout.
- Unexpected memory growth on large runs: use shorter runs (`--tasks`) or process mode with limited parallelism.

## License
MIT. See `LICENSE`.
