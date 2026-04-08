# Integrated Locking Bundle

## Purpose
Provides lock management for editor flows and lock refresh/release APIs used by the admin UI.

Core responsibilities:
- persist lock state via DBAL lock manager
- expose lock refresh endpoint for active editor sessions
- expose lock release endpoint on page unload/navigation
- provide cleanup commands for expired locks

## Install
In this repository the bundle is already wired through `integrated/integrated`.

For host applications:
- ensure bundle registration in `config/bundles.php`
- ensure DB schema includes lock storage used by `Integrated\Common\Locks\Provider\DBAL\Manager`

Legacy host-app setup (pre-Flex/AppKernel style):

```php
// app/AppKernel.php
new Integrated\Bundle\LockingBundle\IntegratedLockingBundle()
```

## Config
This bundle does not define a dedicated Symfony config tree.

Operational defaults are wired through services:
- DB connection service alias: `integrated_locking.dbal.connection` -> `database_connection`
- lock manager service: `integrated_locking.dbal.manager`
- lock table name default: `locks`

Public API routes:
- `POST /_locking/api/refresh`
- `POST /_locking/api/release`

## Commands
- `php bin/console locking:dbal:clean`
- `php bin/console locking:clear`

Command behavior:
- `locking:dbal:clean`: removes expired locks (safe routine cleanup)
- `locking:clear`: removes all locks (operational emergency/reset)

## Cron And Workers
Recommended production cleanup cron:

```cron
*/5 * * * * cd /path/to/app && php bin/console locking:dbal:clean --env=prod -q
```

Increase frequency if editor lock churn is high.

## Verification
- Create an edit lock in admin and confirm periodic refresh calls return `200`.
- Run `php bin/console locking:dbal:clean --env=prod` and confirm expired entries are removed.
- Use `locking:clear` only for controlled reset and validate locks are recreated on next edit.

## Troubleshooting
- `403 Locking is not enabled`: lock manager service is missing or disabled.
- `423 The lock belongs to another user`: lock ownership mismatch by design.
- Frequent stale lock warnings: check client refresh cadence and cleanup cron health.
- Missing command from older docs: `init:locking` is legacy and not provided by this bundle in current versions.

## License
MIT. See `LICENSE`.
