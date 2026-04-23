---
name: create-form-data-provider
description: >
  Create the IdentifiableObject DataProvider that populates the edit form with
  the existing entity's data. Implements `FormDataProviderInterface`, dispatches
  the Get query, and maps the result to the form's expected array structure.
needs: [create-get-query, create-query-handler-interface]
produces: "{Domain}FormDataProvider.php — IdentifiableObject DataProvider that loads entity data for edit form"
---

# create-form-data-provider

## Instructions

1. Create class implementing `FormDataProviderInterface`.
2. `getData($id): array` — dispatch `Get{Domain}ForEditing(new {Domain}Id($id))` via query bus.
3. Map the returned DTO/array to the form's expected field structure.
4. `getDefaultData(): array` — return sensible defaults for the create form (empty strings, null IDs, active=true).
5. For multilingual fields, return arrays keyed by language ID.
6. Inject `QueryBus $queryBus` via constructor.

## Rules

- DataProvider maps query result to form data — it does NOT build commands
- getDefaultData() must return the same structure as getData() — form type cannot distinguish
- Multilingual fields must always return an array keyed by int language ID
