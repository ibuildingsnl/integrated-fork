import { test, expect, Page } from '@playwright/test';

const EDIT_PATH = '/?integrated_website_edit=1';

async function mockSaveEndpoints(page: Page): Promise<void> {
  await page.route('**/*', async (route) => {
    const request = route.request();
    const method = request.method();
    const url = request.url();

    if (method === 'POST' && (url.includes('/pagebuilder/save') || url.includes('/website/menu/save'))) {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ success: true }),
      });
      return;
    }

    await route.continue();
  });
}

test.describe('Toolbar smoke', () => {
  test('editor save flows and dropdown keyboard toggle', async ({ page }) => {
    await mockSaveEndpoints(page);

    await page.goto(EDIT_PATH, { waitUntil: 'domcontentloaded' });

    const commandbar = page.locator('[data-role="integrated-editor-commandbar"]');
    await expect(commandbar).toBeVisible();

    const firstDropdownToggle = page.locator('.integrated-website-toolbar a[data-toggle="dropdown"]').first();
    await firstDropdownToggle.focus();
    await page.keyboard.press('Enter');
    await expect(firstDropdownToggle).toHaveAttribute('aria-expanded', 'true');

    const saveStatus = page.locator('[data-role="integrated-website-save-status"]');
    const saveButton = page.locator('[data-action="integrated-website-page-save"][data-close-editor-on-success="0"]').first();
    await saveButton.click();
    await expect(saveStatus).toHaveAttribute('data-state', 'saved');
    await expect(page).toHaveURL(/integrated_website_edit=1/);

    const saveCloseButton = page.locator('[data-action="integrated-website-page-save"][data-close-editor-on-success="1"]').first();
    await saveCloseButton.click();
    await expect(page).toHaveURL(/integrated_website_edit=0/);
  });
});
