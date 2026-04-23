---
name: create-playwright-bulk-campaign
description: >
  Create the campaign covering bulk operations (bulk enable, disable, delete)
  and the single-row quick-edit status toggle. Verifies the grid reflects the
  changes after each bulk action.
needs: [create-playwright-test-data, create-playwright-resetter, create-grid-action-types, create-admin-routing, create-bulk-delete-command, create-bulk-status-command, create-toggle-status-command]
produces: "03_quickEditAndBulkActions.ts — bulk enable/disable/delete and quick-edit toggle campaign"
conditional: "only if bulk actions were implemented (D11, D12, D13)"
---

# create-playwright-bulk-campaign

## Instructions

1. Create 3 entities.
2. Test quick-edit toggle: click toggle switch on row 1 → verify status changed in row, verify no page reload.
3. Test bulk enable: select rows 1+2, click bulk enable → verify both show active status.
4. Test bulk disable: select rows 1+2, click bulk disable → verify inactive.
5. Test bulk delete: select rows 1+2+3, click bulk delete, confirm modal → verify all 3 gone.
6. `afterAll`: resetter.

## Rules

- Quick-edit (toggle) must verify the change WITHOUT a page reload (AJAX)
- Bulk delete confirmation must be clicked in the modal — not bypassed
