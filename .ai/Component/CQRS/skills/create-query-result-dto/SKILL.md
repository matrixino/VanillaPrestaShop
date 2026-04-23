---
name: create-query-result-dto
description: >
  Create the typed DTO that the `Get{Domain}ForEditing` query handler returns.
  This replaces untyped arrays with a proper PHP object with named, typed
  getters.
needs: [create-get-query, create-query-handler-interface]
produces: "Editable{Domain}.php — typed DTO returned by Get{Domain}ForEditing query handler"
---

# create-query-result-dto

## Instructions

1. Create `src/Core/Domain/{Domain}/QueryResult/Editable{Domain}.php`.
2. Constructor parameters: all fields that the edit form needs to pre-fill.
3. Typed constructor parameters: int/string/bool scalars, `array` for multilingual fields, `?int` for nullable foreign keys.
4. Add a public getter for every field.
5. The class is a pure data object — no methods, no dependencies.

## Rules

- Immutable: no setters, all values set at construction
- Multilingual fields are `array` keyed by language ID
- No ObjectModel instances inside the DTO — only scalars and arrays
