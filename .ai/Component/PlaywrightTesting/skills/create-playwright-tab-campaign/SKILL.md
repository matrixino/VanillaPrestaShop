---
name: create-playwright-tab-campaign
description: >
  Create one Playwright campaign per form tab (as defined in F1 and A3
  manifest). Each campaign creates an entity, edits it to fill all fields in the
  specific tab, saves, and verifies every field value persisted correctly.
needs: [create-playwright-test-data, create-playwright-resetter, create-form-type, create-twig-form-template]
produces: "0{N}_{TabName}.ts — one campaign per form tab verifying all fields in that tab persist"
---

# create-playwright-tab-campaign

## Instructions

1. Number the file based on existing campaign count (e.g., 05, 06 if 04 is the last existing).
2. Create entity with minimal fixture.
3. Navigate to edit, switch to the target tab.
4. Fill EVERY field in that tab using `data{Domain}Full`.
5. Save, assert success flash.
6. Reload the edit page, switch to that tab again.
7. Assert each field retains the expected value.
8. Repeat for multilingual fields in each active language.
9. `afterAll`: resetter.

## Rules

- One campaign per tab — never combine multiple tabs in one campaign
- Verify ALL fields in the tab persist — missing a field assertion hides silent data loss
- Multilingual fields must be verified in at least 2 languages
