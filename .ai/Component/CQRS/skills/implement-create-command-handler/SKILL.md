---
name: implement-create-command-handler
description: >
  Implement the add handler that reads from the Add{Domain}Command and delegates
  to the repository for persistence. Returns the new {Domain}Id.
needs: [create-create-command, create-command-handler-interface, create-doctrine-repository]
produces: "Add{Domain}Handler.php — concrete handler for entity creation"
---

# implement-create-command-handler

## Instructions

1. Create `Add{Domain}Handler.php` implementing `Add{Domain}HandlerInterface`.
2. Constructor injects `{Domain}Repository $repository`.
3. `handle(Add{Domain}Command $command): {Domain}Id` method body:
   a. Construct the ObjectModel or Doctrine entity from command data.
   b. For multilingual fields, map `$command->getLocalizedNames()` to the lang array.
   c. Call `$this->repository->create(...)`.
   d. Return the new `{Domain}Id`.
4. Catch persistence exceptions and rethrow as domain exceptions.
5. Never call another handler — compose at controller level.

## Rules

- Handlers contain business orchestration only — no SQL, no ObjectModel directly
- All DB access goes through the repository
- Never call another handler from within a handler
- Return {Domain}Id from Add handler, void from others
