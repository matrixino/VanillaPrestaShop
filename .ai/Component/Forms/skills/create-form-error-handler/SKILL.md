---
name: create-form-error-handler
description: >
  Documents the complete form error handling flow: server-side validation via
  constraints on form fields, rendering errors in the template, and JavaScript
  navigation to the first tab containing an error.
needs: [create-form-type, create-symfony-admin-controller, create-tab-error-navigator]
produces: "Form validation error display and tab-level error indicators"
---

# create-form-error-handler

## Instructions

1. Add Symfony validation constraints directly on form fields (NotBlank, Length, Regex).
2. In controller: if `!$form->isValid()`, re-render form — errors are displayed automatically by Twig.
3. In Twig template: use `{{ form_errors(form) }}` globally and `{{ form_errors(field) }}` per field.
4. JS: on DOMContentLoaded, scan for `is-invalid` CSS classes inside each tab pane; switch to the first tab with an error.
5. Add `aria-invalid="true"` to invalid fields for accessibility.

## Rules

- Server-side validation is the source of truth — JS validation is enhancement only
- Tab error navigation must activate the FIRST tab with an error, not the last
