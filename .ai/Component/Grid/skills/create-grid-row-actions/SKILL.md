---
name: create-grid-row-actions
description: >
  Documents the PrestaShop row action types (edit link, delete link with
  confirmation, custom actions) and the correct routing configuration.
needs: [create-grid-definition, create-admin-routing]
produces: "RowActionCollection with edit and delete actions wired to correct routes"
---

# create-grid-row-actions

## Instructions

1. `LinkRowAction` for edit: links to `admin_{domain}s_edit` with `{id}` route parameter.
2. `LinkRowAction` for delete: links to `admin_{domain}s_delete`, with `confirm_message` option for JS confirmation.
3. Pass the row's primary key as the `{id}` route parameter.
4. Order: Edit first, Delete last.

## Rules

- Always include a confirmation for delete row action
- Row action links must use the `UrlGenerator` — not hardcoded URLs
