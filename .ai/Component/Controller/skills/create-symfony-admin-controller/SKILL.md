---
name: create-symfony-admin-controller
description: >
  Create the Symfony admin controller that wires all CRUD actions to the
  command/query bus. The controller dispatches commands and queries — it
  contains no business logic.
needs: [create-command-handler-interface, create-query-handler-interface, create-grid-definition, create-form-data-provider, create-form-data-handler]
produces: "{Domain}Controller.php — Symfony admin controller with all CRUD actions"
---

# create-symfony-admin-controller

## Instructions

1. Create controller extending `FrameworkBundleAdminController`.
2. Declare actions: `indexAction`, `createAction`, `editAction`, `deleteAction`.
3. `indexAction`: build grid with `$this->get('prestashop.core.grid.presenter.grid_presenter')`, render `index.html.twig`.
4. `createAction` GET: build empty form via `{Domain}FormDataProvider`, render form template.
5. `createAction` POST: build form, submit, call `{Domain}FormDataHandler->create($form)`, redirect to index with flash.
6. `editAction` GET: load via `Get{Domain}ForEditing` query, populate form, render.
7. `editAction` POST: submit form, call handler `->update($form)`, redirect.
8. `deleteAction`: dispatch `Delete{Domain}Command`, redirect with flash.
9. Add bulk action methods: `bulkDeleteAction`, `bulkEnableStatusAction`, `bulkDisableStatusAction`.
10. Catch typed domain exceptions and display user-friendly flash messages.

## Rules

- ZERO business logic in controllers — delegate everything to handlers via bus
- Never instantiate commands directly with `new` — use the form data handler
- All exceptions from handlers must be caught and turned into flash messages
- Toggle status action must return JSON for AJAX calls
