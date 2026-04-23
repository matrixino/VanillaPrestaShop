---
name: create-bulk-delete-command
description: >
  Command carrying a list of entity IDs for bulk deletion. Dispatched when the
  user selects multiple rows and clicks "Delete selected".
needs: [create-identity-value-object]
produces: "BulkDelete{Domain}sCommand.php"
conditional: "only if the grid has a bulk delete action"
---

# create-bulk-delete-command

## Instructions

1. Check the entity definition Section 4 (grid actions) — confirm bulk delete is required.
2. Constructor takes `array $ids` of `{Domain}Id` (or `array $ids` of int, depending on PS convention — check Carrier).
3. Add `getIds(): array` getter.
4. If the domain convention uses raw int IDs in bulk commands, follow that pattern consistently.
5. Class must be `final` with `declare(strict_types=1)`.

## Rules

- Skip this skill entirely if the grid has no bulk delete action (check the entity definition)
- Be consistent with the domain convention on whether IDs are typed or raw int
- Class is final with declare(strict_types=1)
