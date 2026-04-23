---
name: create-toggle-status-command
description: >
  Command for toggling the active status of a single entity, used by the grid
  toggle switch (AJAX).
needs: [create-identity-value-object]
produces: "Toggle{Domain}ActiveStatusCommand.php — single-row status toggle"
---

# create-toggle-status-command

## Instructions

1. Constructor takes `{Domain}Id $id` and optionally `bool $expectedStatus`.
2. Handler reads current status and flips it, or sets to `$expectedStatus` if provided.
3. Add `getId(): {Domain}Id` getter.
4. Add `getExpectedStatus(): ?bool` getter if `$expectedStatus` is included.
5. Class must be `final` with `declare(strict_types=1)`.

## Rules

- Always use typed {Domain}Id — never raw int
- The optional `$expectedStatus` allows the front-end toggle to specify the desired state explicitly
- Handler is responsible for reading current state if `$expectedStatus` is null
