---
name: create-controller-create-action
description: >
  Documents the create action implementation. GET builds an empty form; POST
  validates, dispatches Add command via form data handler, and redirects with a
  success flash.
needs: [create-symfony-admin-controller, create-form-data-provider, create-form-data-handler, create-create-command]
produces: "createAction() — GET renders empty form, POST dispatches Add command"
---

# create-controller-create-action

## Instructions

1. GET: `$form = $this->createForm({Domain}Type::class)`. Render form template.
2. POST: `$form->handleRequest($request)`. If valid, call `$this->getCommandBus()->handle($formDataHandler->getData($form))`.
3. Catch domain exceptions and add error flash.
4. On success: add `$this->addFlash('success', '...')` and redirect to index.
5. On validation failure: re-render form with errors.

## Rules

- Use the form data handler to convert form data to command — never build command manually in controller
- Flash message keys must use PS translation domain `Admin.Notifications.Success`
