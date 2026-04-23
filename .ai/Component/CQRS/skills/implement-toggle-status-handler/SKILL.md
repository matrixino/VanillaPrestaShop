---
name: implement-toggle-status-handler
description: >
  Load the entity, flip its active status, and save. Used by the grid toggle
  switch via AJAX.
needs: [create-toggle-status-command, create-doctrine-repository]
produces: "Toggle{Domain}ActiveStatusHandler.php — AJAX single-row toggle"
---

# implement-toggle-status-handler

## Instructions

1. Load entity by ID.
2. Flip: `$entity->active = !$entity->active`.
3. Call repository update.
4. Return void.

## Rules

- This handler flips the current value — use BulkToggle when a target value is needed
- Return void — the controller reads back state from the grid, not from this handler
