---
name: create-form-data-handler
description: >
  Create the IdentifiableObject DataHandler that converts the form's submitted
  data into Add or Edit commands and dispatches them to the command bus. This is
  the bridge between the form and the CQRS layer.
needs: [create-create-command, create-edit-command, create-form-data-provider]
produces: "{Domain}FormDataHandler.php — IdentifiableObject DataHandler that dispatches Add/Edit commands from form data"
---

# create-form-data-handler

## Instructions

1. Create class implementing `IdentifiableObjectDataHandlerInterface`.
2. `create(array $data): mixed` — build `Add{Domain}Command` from `$data`, dispatch via command bus, return new `{Domain}Id`.
3. `update($id, array $data): void` — build `Edit{Domain}Command(new {Domain}Id($id))`, call setters for each non-null field, dispatch.
4. For sub-resource fields (e.g. zones), dispatch separate sub-resource commands after the main command.
5. Map form array keys to command setters: `$command->setName($data['name'])`.
6. Handle multilingual fields: `$command->setLocalizedNames($data['name'])` where name is a lang-keyed array.

## Rules

- DataHandler maps form data to commands — it does NOT do persistence directly
- Sub-resource commands are dispatched separately, after the main edit command
- Dispatch order matters for sub-resources: main entity first, then sub-resources
