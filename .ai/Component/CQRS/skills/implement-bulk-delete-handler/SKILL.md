---
name: implement-bulk-delete-handler
description: >
  Loop over the list of IDs and call the single-delete repository method for
  each. Collect errors and throw a bulk exception if any deletions fail.
needs: [create-bulk-delete-command, create-doctrine-repository]
produces: "BulkDelete{Domain}sHandler.php"
conditional: "only if D11 was created"
---

# implement-bulk-delete-handler

## Instructions

1. Iterate `$command->getIds()`.
2. For each ID, call `$this->repository->delete(...)` in a try/catch.
3. Collect any exceptions; continue with remaining IDs.
4. If any failures, throw a bulk exception listing the failed IDs.

## Rules

- Always continue after individual deletion failure — do not abort mid-batch
- Report ALL failed IDs in the bulk exception, not just the first one
- Reuse the same single-delete logic as the single-delete handler via the repository
