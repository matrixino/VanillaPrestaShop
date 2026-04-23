---
name: implement-list-query-handler
description: >
  Implement the list query handler if the domain uses explicit CQRS queries for
  listing. Most PS domains skip this and use the grid QueryBuilder pattern
  instead.
needs: [create-list-query, create-query-handler-interface]
produces: "Get{Domain}sForListingHandler.php or handled by grid QueryBuilder"
conditional: "only if explicit list query exists; most PS domains use SearchCriteria+QueryBuilder"
---

# implement-list-query-handler

## Instructions

1. Check whether domain uses explicit query class or SearchCriteria+QueryBuilder.
2. If explicit: implement handler that calls QueryBuilder with SearchCriteria.
3. Return paginated array of row arrays.

## Rules

- Most domains use the grid QueryBuilder instead of this handler — confirm before creating
- If using SearchCriteria, do not duplicate filtering logic already in the QueryBuilder
