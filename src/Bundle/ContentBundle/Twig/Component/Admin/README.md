# Integrated Admin Components

Public admin component keys in core:

- `integrated_admin:alert_box`
- `integrated_admin:aside_panel`
- `integrated_admin:confirm_modal`
- `integrated_admin:data_table`
- `integrated_admin:detail_list`
- `integrated_admin:empty_state_message`
- `integrated_admin:edit_drawer_panel`
- `integrated_admin:edit_form_shell`
- `integrated_admin:filter_group`
- `integrated_admin:filter_search_input`
- `integrated_admin:folder_menu_panel`
- `integrated_admin:iframe_modal`
- `integrated_admin:options_toolbar`
- `integrated_admin:page_title`
- `integrated_admin:pagination_footer`
- `integrated_admin:row_actions`
- `integrated_admin:section_card`
- `integrated_admin:selection_modal`
- `integrated_admin:status_badge`
- `integrated_admin:taxonomy_category_picker`
- `integrated_admin:workflow_status`

Testing

- Full render tests: extend `Integrated\Bundle\ContentBundle\Tests\Support\AdminComponentKernelTestCase`.
- Lightweight isolated Twig tests: use `Integrated\Bundle\ContentBundle\Tests\Support\InteractsWithIntegratedAdminComponents`.
- Contract coverage: keep `AdminComponentRegistrationTest` and `AdminPublicComponentContractTest` green when adding or renaming public components.

Prop conventions

- Prefer `wrapperClass` for root wrapper classes.
- Prefer `contentHtml` for freeform inner markup slots.
- Prefer `bodyHtml`, `footerHtml`, `actionsHtml` for named areas.
- Prefer `tableClass` for table-only classes on `integrated_admin:data_table`.
- `extraClass` still exists for backwards compatibility; prefer the more specific alias on new usage.

Component boundaries

- Use `page_title`, `section_card`, `data_table`, `pagination_footer`, `row_actions`, `status_badge` as the default admin list stack.
- Use `empty_state_message` for inline empty or secondary feedback in panels, sidebars, and lightweight list views.
- Use `workflow_status` for the color-coded workflow icon bubble wherever the same admin workflow marker appears.
- Use `edit_form_shell` only for the shared edit/delete page shell.
- Use `aside_panel` for hand-built accordion panels, not Symfony form-theme sidebar rows.
- Use `filter_group` for static titled filter/list groups, not collapsible aside panels.
- Use `confirm_modal`, `iframe_modal`, and `selection_modal` as separate modal families; do not fold them back into one generic modal.
- Keep bundle-specific business widgets outside core until the same shell is reused across multiple bundles.

Not in scope for this layer

- Symfony form theme field wrappers in `FormTypeBundle` and `StorageBundle`
- unique navigator views like `content/index_week`
- article search layout
- dashboard-specific widget contracts
