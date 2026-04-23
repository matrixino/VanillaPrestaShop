---
name: implement-delete-command-handler
description: >
  Implement the delete handler. Verifies the entity exists, checks business
  constraints (e.g., cannot delete if referenced by active orders), then calls
  repository delete.
needs: [create-delete-command, create-command-handler-interface, create-doctrine-repository]
produces: "Delete{Domain}Handler.php"
---

# implement-delete-command-handler

## Instructions

1. Load entity to verify existence (throws NotFoundException if not found).
2. Check business constraints (from A1 audit): if entity is referenced, throw `Cannot{Delete}{Domain}Exception`.
3. Call `$this->repository->delete($command->getId(), $shopConstraint)`.

## Rules

- Always verify existence before deletion — never delete blindly
- Business constraint checks come before any deletion attempt
- Use multistore-aware delete via getShopIdsByConstraint
