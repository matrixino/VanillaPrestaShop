---
name: create-grid-column-types
description: >
  Documents all available PrestaShop grid column types and when to use each. The
  correct column type determines the rendering and behavior of each grid cell.
needs: [create-grid-definition]
produces: "Reference for choosing the correct PS grid column type class for each data type"
---

# create-grid-column-types

## Instructions

1. `DataColumn`: plain text display. Use for name, email, date strings.
2. `ToggleColumn`: clickable boolean toggle. Use for `active` status. Requires an AJAX toggle route.
3. `ActionColumn`: renders the row actions dropdown. Always the last column.
4. `PositionColumn`: drag handle for reordering. Use only if entity supports position.
5. `ImageColumn`: displays an image thumbnail. Use for logo/image fields.
6. `BulkActionColumn`: checkbox for row selection. Always the first column.
7. `LinkColumn`: text with a hyperlink. Use for URLs or navigable fields.

## Rules

- ToggleColumn requires a dedicated AJAX route registered in H2
- PositionColumn requires a position save route
- BulkActionColumn must be the first column always
- ActionColumn must be the last column always
