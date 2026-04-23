---
name: implement-bulk-status-handler
description: "Loop over IDs and set `active` status to the target value for each."
needs: [create-bulk-status-command, create-doctrine-repository]
produces: "BulkToggle{Domain}ActiveStatusHandler.php"
conditional: "only if D12 was created"
---

# implement-bulk-status-handler

## Instructions

1. Iterate IDs, load entity, set `active = $command->getExpectedStatus()`, update.
2. Collect and report failures.

## Rules

- Always continue after individual status-update failure — do not abort mid-batch
- Report ALL failed IDs in the bulk exception
- Use the target status from the command — do not flip the current value
