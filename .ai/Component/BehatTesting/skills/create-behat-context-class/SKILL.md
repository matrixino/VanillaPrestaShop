---
name: create-behat-context-class
description: >
  Create the PHPUnit/Behat context class that implements every step definition
  from the feature file. Extends `AbstractDomainFeatureContext` and uses string
  references to track created entities.
needs: [create-behat-feature-file, create-command-handler-interface, create-query-handler-interface]
produces: "{Domain}FeatureContext.php — step definitions implementing all Gherkin scenarios"
---

# create-behat-context-class

## Instructions

1. Create `{Domain}FeatureContext.php` extending `AbstractDomainFeatureContext`.
2. Declare class with `#[BehatContext]` or equivalent registration attribute (check existing contexts).
3. Use `$this->getCommandBus()->handle(new Add{Domain}Command(...))` for write operations.
4. Use `$this->getQueryBus()->handle(new Get{Domain}ForEditing(...))` for read verifications.
5. Track created entities: `$this->sharedStorage->set('carrier_reference_1', $newId->getValue())`.
6. Step for "I add a carrier with name {name}": parse the TableNode or inline parameters, build command, dispatch, store reference.
7. Step for "the carrier {ref} should exist with name {name}": load via query, assert field values.
8. Step for "I should get a {Domain}NotFoundException": use `$this->assertException()` helper.

## Rules

- Always use `$this->sharedStorage` for entity references — never use hardcoded IDs
- All bus calls go through `$this->getCommandBus()` / `$this->getQueryBus()` — never instantiate handlers directly
- Catch typed domain exceptions in error scenarios — not generic \Exception
- Step definitions must be deterministic — no random data, no timing dependencies
