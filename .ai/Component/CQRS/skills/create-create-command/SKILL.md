---
name: create-create-command
description: >
  Create the command that encodes the intention to create a new entity. It
  carries all fields required for creation, validated at construction time via
  value objects or primitive validation.
produces: "Add{Domain}Command.php — write intention carrying all fields for a new entity"
---

# create-create-command

## Instructions

1. Identify the entity fields, actions, and relationships from the legacy code.
2. Create `src/Core/Domain/{Domain}/Command/Add{Domain}Command.php`.
3. Constructor takes all required fields as typed parameters. Optional fields use nullable types with defaults.
4. Multilingual fields (from the entity definition Section 3) take `array $localizedValues` keyed by language ID.
5. Validate primitives in constructor (non-empty strings, positive ints, valid enums). Use value objects where a ValueObject exists.
6. Add typed getters for every property — no public properties.
7. Class must be `final`, no interface needed (commands are data objects, not services).

## Rules

- Commands carry data only — no business logic, no DB calls
- All validation failures throw domain exceptions from the constructor
- Nullable properties represent optional fields that may be omitted on creation
- Sub-resource fields (e.g. zones, ranges) are NOT included here — they get their own command
