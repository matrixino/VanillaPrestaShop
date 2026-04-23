---
name: create-delete-command
description: >
  Minimal command carrying only the entity ID to delete. No other data needed —
  the handler verifies existence before deletion.
needs: [create-identity-value-object]
produces: "Delete{Domain}Command.php — single-entity delete intention"
---

# create-delete-command

## Instructions

1. Create `Delete{Domain}Command.php` with a single constructor parameter: `{Domain}Id $id`.
2. Add `getId(): {Domain}Id` getter.
3. No other properties.

## Rules

- Never accept raw `int` — always use {Domain}Id
- No soft-delete logic here — that goes in the handler
- Class is final with declare(strict_types=1)
