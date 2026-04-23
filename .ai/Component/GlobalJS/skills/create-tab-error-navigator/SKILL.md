---
name: create-tab-error-navigator
description: >
  Create the TypeScript module that, on page load after a form submission with
  validation errors, detects which tab contains invalid fields and activates
  that tab automatically. This prevents users from seeing a success-looking form
  when errors exist in a hidden tab.
needs: [create-ts-entry-point, create-form-type]
produces: "tabErrorNavigator.ts — module that switches to the first form tab containing a server-side validation error"
---

# create-tab-error-navigator

## Instructions

1. Export `initTabErrorNavigator(): void` function.
2. On DOMContentLoaded, query all tab pane elements.
3. For each tab pane, check if it contains any `is-invalid` CSS class (Symfony form error class).
4. If a tab pane has errors, activate it by triggering click on its nav tab or calling the Bootstrap Tab API.
5. Activate only the FIRST tab with errors — stop after finding the first.
6. If no errors found, do nothing.

## Rules

- Only run on form pages that have NavigationTabType tabs
- Must run AFTER Symfony's form error classes are in the DOM (run on DOMContentLoaded)
- Never modify form data — this is read-only navigation only
