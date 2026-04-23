---
name: create-behat-feature-file
description: >
  Create the Gherkin `.feature` file containing the full behavior specification
  for the domain. Each scenario maps to one CRUD or business operation, written
  in Given/When/Then syntax.
produces: "{domain}_management.feature — Gherkin feature file with all scenario stubs"
---

# create-behat-feature-file

## Instructions

1. Create `tests/Integration/Behaviour/Features/Scenario/{Domain}/` directory.
2. Write the feature file header: `Feature: {Domain} management`.
3. Add a `Background:` section to set up a shop context and default language.
4. For each CQRS command (from the entity definition), write a `Scenario:` block: Given setup, When action, Then assertion.
5. Scenario order: Create → Verify → Edit → Verify → Delete → Verify not found.
6. Add error scenarios: create with invalid data, edit non-existent entity, delete in-use entity.
7. For multilingual fields: add scenarios creating entities in multiple languages and verifying per-language values.
8. Use `<reference>` string markers (e.g., `"carrier reference 1"`) not IDs — the context class maps these to actual DB IDs.

## Rules

- Never use numeric IDs in Gherkin — always use string references
- Scenario names must be unique within the feature
- Error scenarios must assert the specific exception or error message
- Background section should be minimal — only shared setup
