---
name: create-controller-delete-action
description: >
  Documents the delete action. Validates CSRF token, dispatches Delete command,
  handles not-found and constraint exceptions, and redirects with flash.
needs: [create-symfony-admin-controller, create-delete-command]
produces: "deleteAction() — dispatches Delete command with CSRF protection"
---

# create-controller-delete-action

## Instructions

1. Validate CSRF token from request.
2. Dispatch `Delete{Domain}Command`.
3. Catch `{Domain}NotFoundException` and `Cannot{Action}{Domain}Exception`.
4. Add appropriate flash (success, warning, or error).
5. Redirect to index.

## Rules

- Always validate CSRF on destructive actions
- Distinguish NotFoundException (entity gone) from ConstraintException (cannot delete due to business rule)
