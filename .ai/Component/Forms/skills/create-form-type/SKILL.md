---
name: create-form-type
description: >
  Create the Symfony form type that renders the multi-tab add/edit form for the
  entity. Uses PrestaShop's `NavigationTabType` for tab organization, not
  standard Symfony tabs.
produces: "{Domain}Type.php — Symfony form type with tab layout containing all form fields"
---

# create-form-type

## Instructions

1. Create `{Domain}Type.php` extending `AbstractType`.
2. `buildForm()`: add each tab as a sub-form embedded using `NavigationTabType` (PS-specific).
3. For each tab, add the corresponding field types.
4. Text fields: `TextType`.
5. Multilingual text fields: `TranslatableType` wrapping `TextType`.
6. Boolean fields: `SwitchType` (PS-specific toggle switch).
7. Select fields: `ChoiceType` with choices provided by a `ChoiceProvider` service (F extra).
8. File upload: `FileType` with allowed MIME types.
9. `configureOptions()`: set `data_class` to the form data class if used.
10. Every tab must be reachable by error — configure tab error CSS class injection (JS5 handles the JS side).

## Rules

- Use NavigationTabType for multi-tab forms — not Symfony's standard tab components
- Sub-forms for tabs use the same form type conventions (buildForm inside the tab form type)
- Form type has no knowledge of commands/queries — it only defines structure and validation
