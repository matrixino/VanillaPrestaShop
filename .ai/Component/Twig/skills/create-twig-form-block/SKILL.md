---
name: create-twig-form-block
description: >
  Create Twig block overrides for form fields that need custom rendering beyond
  Symfony's default form_widget. Common cases: displaying an existing image
  preview next to the upload field, or a custom compound widget.
needs: [create-twig-form-template, create-form-type]
produces: "Twig blocks overriding specific form field rendering (e.g., image preview, custom widget)"
conditional: "only if specific form fields need custom rendering"
---

# create-twig-form-block

## Instructions

1. In form template, override specific field widget: `{% form_theme form 'Admin/{Section}/{Domain}/_form_widgets.html.twig' %}`.
2. In the theme file, override: `{% block _{field_id}_widget %}` with custom HTML.
3. For image preview: show existing image from DataProvider with a "Remove" checkbox below the FileType input.

## Rules

- Form theme overrides are scoped to this form only via `form_theme` — no global side effects
- Keep overrides minimal — Symfony's default rendering handles most cases
