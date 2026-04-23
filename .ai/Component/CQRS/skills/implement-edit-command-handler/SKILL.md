---
name: implement-edit-command-handler
description: >
  Implement the edit handler using the partial-update pattern. Only fields
  explicitly set on the command (non-null) are updated in the database.
needs: [create-edit-command, create-command-handler-interface, create-doctrine-repository]
produces: "Edit{Domain}Handler.php — partial-update handler"
---

# implement-edit-command-handler

## Instructions

1. Load the existing entity via `$this->repository->get{Domain}($command->getId())`.
2. For each field: `if ($command->getName() !== null) { $entity->name = $command->getName(); }`.
3. Apply only non-null fields — never overwrite with null.
4. Call `$this->repository->update($entity)`.
5. Handle sub-resource commands separately (dispatched independently, not composed here).

## Rules

- Check null before every field update — this IS the partial-update pattern
- Never merge sub-resource updates into this handler
- Load then update — never blind update without loading first
