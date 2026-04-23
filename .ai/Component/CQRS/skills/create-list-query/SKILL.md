---
name: create-list-query
description: >
  Create the query used by the grid to retrieve the paginated, filtered, sorted
  list of entities. In most PS domains this is handled by the grid query builder
  rather than an explicit CQRS query; assess which pattern the domain uses.
produces: "Get{Domain}sForListing.php or SearchCriteria-based query — grid data source query"
---

# create-list-query

## Instructions

1. Check if the domain uses an explicit `Get{Domain}sForListing` query or delegates to `SearchCriteria` + grid query builder.
2. For SearchCriteria approach: no explicit query class needed — G2 handles it.
3. For explicit query: create `Get{Domain}sForListing.php` with `SearchCriteria $searchCriteria` parameter.
4. Add getter for search criteria.
5. Create corresponding handler interface in `QueryHandler/` folder.

## Rules

- Most PS grids use the SearchCriteria + QueryBuilder pattern — do not create an unnecessary query class
- If uncertain, examine `src/Core/Domain/Carrier/` for the established pattern
