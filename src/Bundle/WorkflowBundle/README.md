# Integrated Workflow Bundle

## Purpose
Integrates workflow definitions and workflow state transitions into content editing and indexing.

This bundle provides:
- workflow definitions and state entities
- admin workflow routes (`/workflow/*`)
- queue-driven workflow worker processing
- reindex triggers for content affected by workflow changes

## Install
In this repository the bundle is already wired through `integrated/integrated`.

For host applications:
- ensure bundle registration in `config/bundles.php`
- ensure workflow routing import is enabled
- run database migrations for workflow entities

Legacy host-app setup (pre-Flex/AppKernel style):

```yaml
# app/config/routing.yml
intergrated_workflow:
  resource: "@IntegratedWorkflowBundle/Resources/config/routing.xml"
```

```yaml
# optional security recommendation from legacy docs
access_decision_manager:
  strategy: unanimous
```

## Config
Main config node: `integrated_workflow`.

```yaml
# config/packages/integrated_workflow.yaml
integrated_workflow:
  email: "mailer@integratedforpublishers.com"
```

Operational notes:
- queue name for worker processing is `workflow-worker` (`integrated_queue.workflow`)
- bundle prepends Twig form theme `@IntegratedWorkflow/form/form_div_layout.html.twig`

## Commands
- `php bin/console workflow:index [id ...] [--full] [--ignore]`
- `php bin/console workflow:worker:run [--batch=<n>]`

Command behavior:
- `workflow:index` resolves workflow definitions to affected content types and dispatches `solr:indexer:queue`.
- `workflow:worker:run` pulls queue payloads and runs child console commands for:
  - workflow index updates
  - full workflow index updates
  - optional channel-delete integration payloads

## Cron And Workers
Recommended production scheduler:

```cron
# process workflow queue continuously in small batches
* * * * * cd /path/to/app && php bin/console workflow:worker:run --batch=10 --env=prod -q
```

If workflow changes must be reflected faster, lower cron interval via external scheduler or increase batch size carefully.

## Verification
- Trigger full workflow index: `php bin/console workflow:index --full --env=prod`
- Process queued workflow messages: `php bin/console workflow:worker:run --env=prod`
- Confirm related content gets queued for Solr indexing (`solr:indexer:queue`)

## Troubleshooting
- `workflow:index` fails on unknown ids: use valid ids or `--ignore`.
- Worker appears idle: verify queue `workflow-worker` is receiving messages.
- Reindex not visible: ensure Solr commands (`solr:indexer:run`, `solr:worker:run`) are also scheduled.
- Duplicate worker runs: command is lock-protected; keep a single scheduler entry.

## License
MIT. See `LICENSE`.
