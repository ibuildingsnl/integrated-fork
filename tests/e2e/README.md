# Toolbar E2E Smoke

Run the toolbar editor smoke tests with:

```bash
npx --yes @playwright/test@1.52.0 test tests/e2e/toolbar.spec.ts --config=playwright.config.ts
```

Set `TOOLBAR_E2E_BASE_URL` to target a specific website/editor instance.

In CI, set `PLAYWRIGHT_BASE_URL` to enable the optional E2E stage in `jenkins.build`.
