---
name: create-query-handler-interface
description: >
  Create the query handler interfaces for all read operations. These define what
  return types the handlers must provide.
needs: [create-get-query, create-list-query]
produces: "Handler interfaces in src/Core/Domain/{Domain}/QueryHandler/"
---

# create-query-handler-interface

## Instructions

1. Create `Get{Domain}ForEditingHandlerInterface.php` extending `QueryHandlerInterface`.
2. Method: `public function handle(Get{Domain}ForEditing $query): Editable{Domain}` (or array, per domain convention).
3. If list query exists, create corresponding interface.

## Rules

- Query handlers return data — never void
- Return types should be typed DTOs or arrays, not ObjectModel instances
