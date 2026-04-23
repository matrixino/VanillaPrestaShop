---
name: create-sub-resource-command
description: >
  Command for replacing the full collection of a sub-resource (e.g., carrier
  zones, carrier ranges). Always uses atomic replace — delete all then insert
  all — never partial merge.
needs: [create-identity-value-object]
produces: "Set{Domain}{SubResource}sCommand.php — atomic replace command for a sub-resource collection"
conditional: "only if domain has sub-resources (has-many relations)"
---

# create-sub-resource-command

## Instructions

1. Check the entity definition Section 6 (sub-resources) — one command per sub-resource type.
2. Constructor takes `{Domain}Id` and the full replacement collection (array or typed collection).
3. Handler deletes all existing sub-resources then inserts the new set.
4. Never use partial update for sub-resources — always full replace.
5. Add `getId(): {Domain}Id` and `get{SubResource}s(): array` getters.
6. If multiple sub-resource types exist, create one command class per sub-resource type.

## Rules

- One command per sub-resource type
- The collection replaces the entire set — partial update is not supported
- If the user sends an empty array, all sub-resources are deleted
- Skip this skill entirely if the domain has no has-many sub-resources (check the entity definition)
