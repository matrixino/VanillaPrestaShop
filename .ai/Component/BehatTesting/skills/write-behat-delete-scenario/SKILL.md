---
name: write-behat-delete-scenario
description: >
  Write the delete scenario and its step definitions. Tests both successful
  deletion and the not-found case after deletion.
needs: [write-behat-create-scenario, create-delete-command]
produces: "Delete{Domain} scenario in feature file + step definitions"
---

# write-behat-delete-scenario

## Instructions

1. `When I delete {domain} "carrier_1"`.
2. `Then {domain} "carrier_1" should not exist`.
3. In step implementation: dispatch DeleteCommand, then verify Get query throws NotFoundException.
4. Add error scenario: `When I delete non-existent {domain} "ghost_ref" Then I should get {Domain}NotFoundException`.

## Rules

- After deletion, always verify via query that the entity no longer exists
- Use typed NotFoundException from D7 — not a generic assertion on null
