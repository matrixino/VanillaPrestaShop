---
name: create-position-column
description: >
  Documents how to add drag-and-drop row reordering to a PrestaShop grid.
  Requires a PositionColumn in the definition, a dedicated update-position
  route, and position handling in the repository.
needs: [create-grid-definition, create-admin-routing]
produces: "PositionColumn configuration and position-update route wiring"
conditional: "only for entities with position/sort support"
---

# create-position-column

## Instructions

1. Add `PositionColumn` as the second column (after BulkActionColumn) in the Grid Definition.
2. Configure the position update route: `->setOption('update_method', 'POST')->setOption('update_route', 'admin_{domain}s_update_position')`.
3. Create the `admin_{domain}s_update_position` POST route in the routing YAML (see `create-admin-routing` skill).
4. In the controller, handle the AJAX position update: receive `positions[]` array, dispatch `UpdatePosition{Domain}Command` (or use QueryBuilder directly).
5. In the repository, update the `position` column for the moved entities.

## Rules

Column ordering conventions (PositionColumn as second column) are in [Grid/CONTEXT.md](../../CONTEXT.md#column-definitions). Skill-specific reminders:

- Position updates are always AJAX — return JSON response, not redirect
- Position values start at 0 or 1 — be consistent with existing PS convention
