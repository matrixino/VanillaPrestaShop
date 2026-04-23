---
name: create-grid-bulk-actions
description: >
  Documents the PrestaShop bulk action types and the correct way to wire them to
  form submission routes. Bulk actions submit a form containing the selected row
  IDs.
needs: [create-grid-definition, create-admin-routing]
produces: "BulkActionCollection with enable/disable/delete wired to correct routes"
---

# create-grid-bulk-actions

## Instructions

1. `SubmitBulkAction`: submits the grid form to a controller action. Use for enable/disable/delete.
2. Configure: `->setName('bulk_enable')->setOptions(['submit_route' => 'admin_{domain}s_bulk_enable_status'])`.
3. The form submission sends `{domain}BulkAction[]` array of selected IDs to the route.
4. Confirmation modal for bulk delete: add `confirm_bulk_action: true` to options.

## Rules

- Bulk delete must have a confirmation dialog
- Bulk action route names must match H2 exactly
