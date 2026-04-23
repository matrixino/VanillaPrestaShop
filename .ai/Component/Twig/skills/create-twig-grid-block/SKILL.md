---
name: create-twig-grid-block
description: >
  Create a Twig block that overrides specific grid column renderers when the
  default PrestaShop column rendering is insufficient for the domain. Most grids
  do not need this — only add it if a column requires custom HTML.
needs: [create-twig-index-template, create-grid-definition]
produces: "Twig block overriding grid panel rendering for domain-specific customizations"
conditional: "only if the grid needs domain-specific column rendering beyond defaults"
---

# create-twig-grid-block

## Instructions

1. Extend or use `@PrestaShop/Admin/Common/Grid/grid_panel.html.twig`.
2. Override specific column block: `{% block column_{column_id}_content %}`.
3. Render custom HTML for that column using the row data.
4. For image columns: `<img src="{{ imageUrl }}" alt="">`.

## Rules

- Only override columns that need custom rendering — leave defaults alone
- Never hardcode image paths — use the PS link helper or Twig extension
