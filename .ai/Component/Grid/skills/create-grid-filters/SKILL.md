---
name: create-grid-filters
description: >
  Create the Symfony Form type that renders the grid filter bar. Each filter
  corresponds to a column that supports filtering, with the appropriate input
  widget (text input, select, date range).
needs: [create-grid-definition]
produces: "{Domain}GridFilters.php — filter form type for the grid search bar"
---

# create-grid-filters

## Instructions

1. Create `{Domain}GridFilters.php` extending `AbstractType` (or `FiltersType`).
2. `buildForm()`: add one field per filterable column using the appropriate Symfony form type.
3. Text filter: `TextType`, `required: false`.
4. Status/boolean filter: `ChoiceType` with `['' => 'All', '1' => 'Yes', '0' => 'No']`.
5. Date range filter: `DateRangeType` (PrestaShop-specific).
6. Field names must match the filter IDs defined in G1's `getFilters()`.
7. No validation constraints on filter fields — all are optional.

## Rules

- Filter field names must match G1 filter IDs exactly
- All filter fields are optional (required: false)
- Do not add filters for columns that are not declared as filterable in G1
