---
name: create-playwright-position-campaign
description: >
  Create the campaign that verifies drag-and-drop row reordering in the grid.
  Reads initial positions, drags row 1 to position 2, and verifies the new
  order.
needs: [create-playwright-test-data, create-playwright-resetter, create-grid-definition]
produces: "04_changePosition.ts — drag-and-drop position reorder campaign"
conditional: "only if domain has PositionColumn in grid"
---

# create-playwright-position-campaign

## Instructions

1. Create at least 3 entities with distinct names.
2. Read initial order: get all row names in order.
3. Drag first row to second position using `page.dragAndDrop()`.
4. Verify row order changed: first name is now second, second is now first.
5. Reload page and verify new order persisted to DB.
6. `afterAll`: resetter.

## Rules

- Always verify persistence by reloading the page after drag
- Use `page.dragAndDrop()` with the position drag handle selector
