# Integrated Channel Bundle

## Purpose
Provides channel connector configuration and queue-based content distribution/export.

Core responsibilities:
- connector config CRUD in admin
- adapter registration and config resolution
- channel distribution queue handling
- export worker command for queued channel messages

## Install
In this repository the bundle is already wired through `integrated/integrated`.

For host applications:
- ensure bundle registration in `config/bundles.php`
- ensure routing import is active for connector config pages
- ensure queue provider used by `integrated_queue.factory` is configured

Legacy host-app setup (pre-Flex/AppKernel style):

```php
// app/AppKernel.php
new Integrated\Bundle\ChannelBundle\IntegratedChannelBundle()
```

## Config
Main config node: `integrated_channel`.

Example:

```yaml
# config/packages/integrated_channel.yaml
integrated_channel:
  configs:
    example_connector:
      enabled: true
      adaptor: app.channel_adapter.example
      options: { }
      channel: [main]
```

Notes:
- `channel` accepts string or list and is normalized to an array.
- Twig form theme `@IntegratedChannel/form/options.html.twig` is prepended automatically.
- Queue name for distribution/export is `channel-distribution`.

Admin config routes (via bundle routing import):
- `/connector/config/`
- `/connector/config/new/{adapter}`
- `/connector/config/{id}`

## Commands
- `php bin/console channel:export [--full] [--daemon] [--wait=<ms>]`

Command behavior:
- default mode: execute one exporter pass (`exportMessages()`)
- `--full`: keep running until queue is empty
- `--daemon`: run continuously until stopped
- `--wait`: sleep between loops for `--full`/`--daemon`

## Cron And Workers
Recommended production scheduler:

```cron
*/5 * * * * cd /path/to/app && php bin/console channel:export --env=prod -q
```

For high-throughput channels:

```cron
*/5 * * * * cd /path/to/app && php bin/console channel:export --full --env=prod -q
```

## Verification
- Queue one known export payload.
- Run `php bin/console channel:export --env=prod`.
- Confirm processed message count in output/logs.
- Verify downstream connector side-effects (API push, feed write, etc.).

## Troubleshooting
- Export keeps failing: inspect application logs for `Channel Export Error`.
- Queue not draining: use `--full` and verify no parallel workers are contending.
- Connector form issues: validate adapter service id and required options in config.
- No messages exported: confirm distribution listeners are creating queue payloads.

## License
MIT. See `LICENSE`.
