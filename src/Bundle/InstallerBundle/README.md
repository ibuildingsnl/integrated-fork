# InstallerBundle

`InstallerBundle` orchestrates project bootstrap and install tasks.

## Key features

- Command: `integrated:install`
- Step-based execution (`--step[]`):
  - `tests`
  - `cache`
  - `assets`
  - `migrations`

## Main entry points

- Command: `Command/IntegratedInstallCommand.php`
- DI extension: `DependencyInjection/IntegratedInstallerExtension.php`
- Services: `Resources/config/services.xml`

## Tests

- `Test/`

## Suggested improvements

1. Improve per-step failure signaling (strict exit codes).
2. Add idempotency tests for repeated installs.
3. Add clearer CI-oriented command output sections.
