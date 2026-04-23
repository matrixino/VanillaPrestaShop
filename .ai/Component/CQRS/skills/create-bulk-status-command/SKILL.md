---
name: create-bulk-status-command
description: >
  Command for bulk enabling or disabling multiple entities. Takes a list of IDs
  and a boolean target status.
needs: [create-identity-value-object]
produces: "BulkToggle{Domain}ActiveStatusCommand.php"
conditional: "only if the grid has a bulk enable/disable action"
---

# create-bulk-status-command

## Instructions

1. Check the entity definition Section 4 — confirm bulk enable/disable actions are required.
2. Constructor takes `array $ids` (of {Domain}Id or int) and `bool $expectedStatus`.
3. Add typed getters: `getIds(): array` and `getExpectedStatus(): bool`.
4. Class must be `final` with `declare(strict_types=1)`.
5. Follow the same ID type convention (typed vs raw int) used in D11 for consistency.

## Rules

- Skip if the grid has no bulk enable/disable action (check the entity definition)
- `$expectedStatus = true` means enable; `false` means disable
- Be consistent with D11 on whether IDs are typed {Domain}Id or raw int
