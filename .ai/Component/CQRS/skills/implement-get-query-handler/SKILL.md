---
name: implement-get-query-handler
description: >
  Implement the query handler that loads the entity and maps it to the edit DTO
  or array returned to the form data provider.
needs: [create-get-query, create-query-handler-interface, create-doctrine-repository]
produces: "Get{Domain}ForEditingHandler.php — returns populated edit DTO"
---

# implement-get-query-handler

## Instructions

1. Inject `{Domain}Repository` and any needed lang/shop context.
2. Load entity: `$entity = $this->repository->get{Domain}($query->getId())`.
3. Map all fields to the return DTO or array: scalar fields, multilingual fields (array keyed by langId), related IDs.
4. Return typed DTO or associative array, per domain convention.

## Rules

- No write side effects in query handlers
- Map ALL editable fields — missing fields cause form to show empty on edit
