---
name: create-get-query
description: >
  Create the query that retrieves all fields of a single entity for the edit
  form. Returns a typed `Editable{Domain}` DTO (not an array), populated by the
  adapter handler.
needs: [create-identity-value-object]
produces: "Get{Domain}ForEditing.php — query returning a typed DTO for the edit form"
---

# create-get-query

## Instructions

1. Create `Get{Domain}ForEditing.php` with `{Domain}Id $id` constructor parameter.
2. Add `getId(): {Domain}Id` getter.
3. Optionally create a corresponding `Editable{Domain}.php` DTO in `QueryResult/` with all editable fields typed. Alternatively the handler can return an array — check existing PS patterns.
4. Check `src/Core/Domain/Carrier/Query/` as reference for whether PS uses DTO or array return.

## Rules

- Queries are read-only — they never trigger side effects
- The query class itself is a data object, not a service
- Return type of the handler interface is either the DTO class or `array` — be consistent with domain convention
