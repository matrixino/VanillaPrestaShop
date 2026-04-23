---
name: write-behat-create-scenario
description: >
  Write the Gherkin scenario and corresponding step definitions for the create
  operation. Covers happy path and validation error cases.
needs: [create-behat-feature-file, create-behat-context-class, create-create-command]
produces: "Add{Domain} scenario in feature file + step definitions in context class"
---

# write-behat-create-scenario

## Instructions

1. Happy path: `When I add a {domain} with following properties: | name | Test {Domain} |` → `Then {domain} "carrier_1" should be created`.
2. Validation error: `When I add a {domain} with empty name Then I should get a CannotCreate{Domain}Exception`.
3. In context class: implement `iAddADomainWith(TableNode $table)` — parse table, build Add{Domain}Command.
4. Assert the new ID was stored in sharedStorage.
5. Implement `domainRefShouldBeCreated(string $ref)` using Get{Domain}ForEditing query.

## Rules

- Always store the new entity reference in sharedStorage after creation
- Test both the happy path and at least one validation failure
