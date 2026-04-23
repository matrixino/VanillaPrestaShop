---
name: create-grid-definition
description: >
  Create the Grid Definition Factory that declares all columns, bulk actions,
  and row actions for the entity listing page. This factory is the single source
  of truth for the grid structure.
produces: "{Domain}GridDefinitionFactory.php — defines columns, filters, actions for the entity listing"
---

# create-grid-definition

## Instructions

1. Create `{Domain}GridDefinitionFactory.php` extending `AbstractGridDefinitionFactory`.
2. Override `getId(): string` — return a unique grid ID (e.g., `'carrier'`).
3. Override `getName(): string` — return translatable grid name.
4. Implement `getColumns(): ColumnCollection` — add each column from the entity definition using column type classes.
5. For each column, choose the correct type: `DataColumn` (text), `ToggleColumn` (boolean with AJAX toggle), `ActionColumn` (row actions), `PositionColumn` (drag-and-drop reorder, if applicable).
6. Implement `getFilters(): FilterCollection` — add filters matching the columns.
7. Implement `getBulkActions(): BulkActionCollection` — add enable, disable, delete bulk actions.
8. Implement `getRowActions(): RowActionCollection` — add edit and delete row actions.
9. Register the factory in DI.

## Rules

- Every column must have a unique ID matching the QueryBuilder alias
- ToggleColumn requires an AJAX route in the actions config
- PositionColumn is only added when the entity supports drag-and-drop reordering
- Column IDs must be snake_case matching the SQL query result column alias
