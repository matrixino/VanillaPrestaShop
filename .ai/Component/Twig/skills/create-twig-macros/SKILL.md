---
name: create-twig-macros
description: >
  Documents when and how to create Twig macros for domain-specific template
  fragments that appear in multiple places. Prefer inline templates for
  single-use fragments — only extract to macros when used 3+ times.
produces: "Domain-specific Twig macros for reusable template fragments"
conditional: "only if index or form templates have reusable fragments worth extracting"
---

# create-twig-macros

## Instructions

1. Create `macros.html.twig` with `{% macro name(args) %}...{% endmacro %}`.
2. Import in templates: `{% import '@PrestaShop/Admin/{Section}/{Domain}/macros.html.twig' as macros %}`.
3. Use: `{{ macros.name(arg) }}`.

## Rules

- Create macros only for fragments used in 3+ places
- Macros must not access global Twig variables — pass all data as arguments
