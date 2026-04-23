---
name: write-behat-edit-scenario
description: >
  Write the Gherkin scenario and step definitions for the edit operation.
  Requires an entity created in B3 (via sharedStorage reference).
needs: [write-behat-create-scenario, create-edit-command]
produces: "Edit{Domain} scenario in feature file + step definitions"
---

# write-behat-edit-scenario

## Instructions

1. `Given {domain} "carrier_1" exists` (uses ref from B3).
2. `When I edit {domain} "carrier_1" with name "Updated Name"`.
3. `Then {domain} "carrier_1" should have name "Updated Name"`.
4. Implement partial-update step: build Edit{Domain}Command with only the fields being edited.
5. Verify unchanged fields remain unchanged.

## Rules

- Do not recreate an entity in edit scenario — use the ref created in B3
- Test that only specified fields change (partial update validation)
