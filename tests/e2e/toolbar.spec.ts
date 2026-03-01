import { test, expect, Page } from '@playwright/test';

const EDIT_PATH = '/?integrated_website_edit=1';

type SaveRequest = {
  url: string;
  body: Record<string, unknown>;
};

type SaveMockOptions = {
  pageBuilderResponder?: (index: number, requestBody: Record<string, unknown>) => { status: number; body: Record<string, unknown> };
};

async function makeEditorDirty(page: Page): Promise<void> {
  await page.evaluate(() => {
    const wrapper = document.querySelector('.integrated-website-sortable[data-block-type="block"] > .integrated-block');
    if (wrapper) {
      wrapper.classList.add('e2e-dirty');
    }
    document.dispatchEvent(new CustomEvent('integrated-editor-content-change', { detail: { source: 'e2e' } }));
  });
}

async function mockSaveEndpoints(page: Page, options: SaveMockOptions = {}): Promise<{ pageBuilderRequests: SaveRequest[] }> {
  const pageBuilderRequests: SaveRequest[] = [];
  let pageBuilderRequestCount = 0;

  await page.route('**/*', async (route) => {
    const request = route.request();
    const method = request.method();
    const url = request.url();
    const rawBody = request.postData() || '{}';

    let parsedBody: Record<string, unknown> = {};
    try {
      parsedBody = JSON.parse(rawBody);
    } catch (error) {
      parsedBody = {};
    }

    if (method === 'POST' && url.includes('/pagebuilder/save')) {
      pageBuilderRequestCount++;
      pageBuilderRequests.push({ url, body: parsedBody });

      const response = options.pageBuilderResponder
        ? options.pageBuilderResponder(pageBuilderRequestCount, parsedBody)
        : {
            status: 200,
            body: { success: true, revision: 1 },
          };

      await route.fulfill({
        status: response.status,
        contentType: 'application/json',
        body: JSON.stringify(response.body),
      });
      return;
    }

    if (method === 'POST' && url.includes('/website/menu/save')) {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ success: true }),
      });
      return;
    }

    await route.continue();
  });

  return { pageBuilderRequests };
}

test.describe('Toolbar smoke', () => {
  test('editor save flows, dirty-state toggle, and dropdown keyboard support', async ({ page }) => {
    const requests = await mockSaveEndpoints(page);

    await page.goto(EDIT_PATH, { waitUntil: 'domcontentloaded' });

    const commandbar = page.locator('[data-role="integrated-editor-commandbar"]');
    await expect(commandbar).toBeVisible();

    const saveStatus = page.locator('[data-role="integrated-website-save-status"]');
    const saveButton = page.locator('[data-action="integrated-website-page-save"][data-close-editor-on-success="0"]').first();
    const saveCloseButton = page.locator('[data-action="integrated-website-page-save"][data-close-editor-on-success="1"]').first();

    await expect(saveButton).toHaveAttribute('aria-disabled', 'true');

    const firstDropdownToggle = page.locator('.integrated-website-toolbar a[data-toggle="dropdown"]').first();
    await firstDropdownToggle.focus();
    await page.keyboard.press('Enter');
    await expect(firstDropdownToggle).toHaveAttribute('aria-expanded', 'true');

    await makeEditorDirty(page);
    await expect(saveStatus).toHaveAttribute('data-state', 'dirty');
    await expect(saveButton).toHaveAttribute('aria-disabled', 'false');

    await saveButton.click();
    await expect(saveStatus).toHaveAttribute('data-state', 'saved');
    await expect(saveButton).toHaveAttribute('aria-disabled', 'true');
    await expect(page).toHaveURL(/integrated_website_edit=1/);
    await expect.poll(() => requests.pageBuilderRequests.length).toBeGreaterThan(0);
    await expect(requests.pageBuilderRequests[0]?.body?.expectedRevision).toBe(0);
    await expect(requests.pageBuilderRequests[0]?.body?.force).toBe(false);

    await makeEditorDirty(page);
    await expect(saveButton).toHaveAttribute('aria-disabled', 'false');
    await saveCloseButton.click();
    await expect(page).toHaveURL(/integrated_website_edit=0/);
  });

  test('conflict response offers overwrite path with force save', async ({ page }) => {
    const requests = await mockSaveEndpoints(page, {
      pageBuilderResponder: (index) => {
        if (index === 1) {
          return {
            status: 409,
            body: {
              success: false,
              conflict: true,
              currentRevision: 3,
            },
          };
        }

        return {
          status: 200,
          body: {
            success: true,
            revision: 4,
          },
        };
      },
    });

    await page.goto(EDIT_PATH, { waitUntil: 'domcontentloaded' });
    await makeEditorDirty(page);

    page.on('dialog', async (dialog) => {
      await dialog.dismiss();
    });

    const saveButton = page.locator('[data-action="integrated-website-page-save"][data-close-editor-on-success="0"]').first();
    const saveStatus = page.locator('[data-role="integrated-website-save-status"]');
    await saveButton.click();

    await expect(saveStatus).toHaveAttribute('data-state', 'saved');
    await expect.poll(() => requests.pageBuilderRequests.length).toBe(2);
    await expect(requests.pageBuilderRequests[0]?.body?.force).toBe(false);
    await expect(requests.pageBuilderRequests[1]?.body?.force).toBe(true);
    await expect(requests.pageBuilderRequests[1]?.body?.expectedRevision).toBe(3);
  });

  test('unsaved changes guard blocks close until confirmed', async ({ page }) => {
    await mockSaveEndpoints(page);
    await page.goto(EDIT_PATH, { waitUntil: 'domcontentloaded' });
    await makeEditorDirty(page);

    let firstDialog = true;
    page.on('dialog', async (dialog) => {
      if (firstDialog) {
        firstDialog = false;
        await dialog.dismiss();
        return;
      }

      await dialog.accept();
    });

    const closeButton = page.locator('.integrated-editor-close');
    await closeButton.click();
    await expect(page).toHaveURL(/integrated_website_edit=1/);

    await closeButton.click();
    await expect(page).toHaveURL(/integrated_website_edit=0/);
  });
});
