---
name: create-behat-shared-storage
description: >
  Documents the entity reference management pattern used across all Behat
  contexts. String references like "carrier_1" are stored in SharedStorage and
  resolved to integer IDs, allowing scenarios to reference entities without
  coupling to DB IDs.
needs: [create-behat-context-class]
produces: "SharedStorage usage pattern for entity reference management across scenarios"
---

# create-behat-shared-storage

## Instructions

1. After creating an entity via command bus, call: `$this->sharedStorage->set('carrier_1', $newId->getValue())`.
2. To retrieve: `$id = new CarrierId($this->sharedStorage->get('carrier_1'))`.
3. For complex objects: store the full response DTO if multiple assertions are needed.
4. Clear storage between feature files using `@reset` tag or Background step.
5. Reference naming convention: `{domain}_{n}` (e.g., `carrier_1`, `carrier_2`) for ordered creation.

## Rules

- NEVER hardcode integer IDs in step definitions
- References must be set before they can be resolved — ordering matters
- Use descriptive reference names that reflect the entity's test role
- Storage keys must be unique within a scenario — use a consistent naming convention
