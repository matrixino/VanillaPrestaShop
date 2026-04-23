---
name: create-playwright-filter-campaign
description: >
  Create the Playwright campaign that verifies all grid filters and sortable
  columns. Creates known test data, applies each filter, verifies filtered
  results, resets, and re-verifies.
needs: [create-playwright-test-data, create-playwright-resetter, create-grid-filters, create-admin-routing]
produces: "02_filterSort{Domain}s.ts — filter and sort campaign for the grid"
---

# create-playwright-filter-campaign

## Instructions

1. Create 3 entities with distinct values for each filterable field.
2. For each filter:
   a. Enter filter value (text, select, date range).
   b. Submit filter.
   c. Assert only matching rows visible.
   d. Assert non-matching rows absent.
   e. Reset filter, assert all rows visible.
3. For each sortable column: click sort ascending, verify order; click sort descending, verify reverse order.
4. `afterAll`: resetter cleanup.

## Rules

- Create test data with values that produce distinct, verifiable filter results
- Test filter reset as explicitly as filter apply
