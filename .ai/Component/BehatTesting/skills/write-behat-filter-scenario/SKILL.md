---
name: write-behat-filter-scenario
description: >
  Write Behat scenarios that verify the grid filtering and search functionality.
  Creates test data and verifies filtered results.
needs: [create-behat-context-class, create-grid-query-builder]
produces: "Filter and search scenarios in the feature file"
---

# write-behat-filter-scenario

## Instructions

1. Create multiple entities with different names, statuses, etc.
2. Apply a filter: `When I filter {domain}s by name "Test"`.
3. Assert only matching entities appear: `Then I should see {domain} "carrier_1" in the list`.
4. Assert non-matching entities are absent.
5. Reset filter and verify all entities appear again.

## Rules

- Create predictable test data with distinct values per filter field
- Test at least 2 different filter types
- Always reset filters between scenarios to prevent state leakage
