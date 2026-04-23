---
name: register-grid-service
description: >
  Register the grid components (definition factory, query builder, filters form
  type) in the Symfony DI container with the required tags and constructor
  arguments.
needs: [create-grid-definition, create-grid-query-builder, create-grid-filters]
produces: "DI YAML registrations for grid definition factory, query builder, and filters"
---

# register-grid-service

## Instructions

1. Register `{Domain}GridDefinitionFactory` with tag `prestashop.core.grid.definition.factory` and attribute `grid_id: {domain}`.
2. Register `{Domain}QueryBuilder` with `autowire: true` and inject `DBAL connection` if needed.
3. Register `{Domain}GridFilters` form type with tag `form.type`.
4. Run `php bin/console debug:container | grep {domain}` to verify.

## Rules

- Grid definition factory tag `grid_id` must match the ID returned by `getId()` in G1
- All constructor dependencies must be explicitly wired or autowired
