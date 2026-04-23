---
name: create-command-handler-interface
description: >
  Create the handler interface for each command. These interfaces live in Core
  and define the contract — the concrete implementations in Adapter are
  registered to satisfy these interfaces.
needs: [create-create-command, create-edit-command, create-delete-command, create-bulk-delete-command, create-bulk-status-command, create-toggle-status-command]
produces: "Handler interfaces in src/Core/Domain/{Domain}/CommandHandler/ — contracts for all write operations"
---

# create-command-handler-interface

## Instructions

1. For each command (Add, Edit, Delete, BulkDelete, BulkToggleStatus, ToggleStatus), create a corresponding `{Action}{Domain}HandlerInterface.php`.
2. Each interface extends `CommandHandlerInterface` (from PrestaShop Core).
3. Single method: `public function handle({Action}{Domain}Command $command): void` (or return type if handler produces a result — e.g., Add returns {Domain}Id).
4. Interfaces live in `src/Core/Domain/{Domain}/CommandHandler/`.
5. Add handler interfaces only for commands that actually exist.

## Rules

- `Add{Domain}Handler` typically returns `{Domain}Id` (the new entity's ID)
- Edit, Delete, Bulk handlers return `void`
- One interface per command — never combine multiple commands in one interface
