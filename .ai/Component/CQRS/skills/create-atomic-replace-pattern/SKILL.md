---
name: create-atomic-replace-pattern
description: >
  Documents the atomic replace pattern used for all sub-resource collections
  (ranges, zones, groups). Always deletes all rows then inserts the new set in a
  transaction.
needs: [implement-sub-resource-handler, create-sub-resource-command]
produces: "Reference implementation of the atomic delete-all+insert-all pattern for sub-resource collections"
conditional: "only for domains with sub-resources"
---

# create-atomic-replace-pattern

## Instructions

1. Open a DB transaction before any sub-resource modification.
2. Execute: `DELETE FROM ps_{sub_table} WHERE id_{domain} = :id`.
3. For each item in the new collection, execute an INSERT.
4. Commit on success; rollback on any failure.
5. Never do partial updates (UPDATE WHERE id = ?) — always replace the full set.

## Rules

- Transaction wrapping is mandatory — partial replace corrupts data
- Empty input collection means "remove all sub-resources" — this is valid
- Log a warning if the collection is suspiciously large (implementation detail)
