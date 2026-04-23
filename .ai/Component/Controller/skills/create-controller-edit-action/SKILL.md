---
name: create-controller-edit-action
description: >
  Documents the edit action. GET loads the entity via Get query and pre-fills
  the form using the DataProvider; POST dispatches the Edit command via
  FormDataHandler.
needs: [create-symfony-admin-controller, create-form-data-provider, create-form-data-handler, create-edit-command, create-get-query]
produces: "editAction() — GET loads entity into form, POST dispatches Edit command"
---

# create-controller-edit-action

## Instructions

1. GET: dispatch `Get{Domain}ForEditing` query → pass result to `{Domain}FormDataProvider->getData($id)` → pre-fill form.
2. POST: handle request, validate form, call FormDataHandler update method, redirect on success.
3. Catch `{Domain}NotFoundException` → add error flash, redirect to index.

## Rules

- Always catch NotFoundException before dispatching edit — entity may have been deleted concurrently
- FormDataProvider must transform the query result into form-ready data, not the controller
