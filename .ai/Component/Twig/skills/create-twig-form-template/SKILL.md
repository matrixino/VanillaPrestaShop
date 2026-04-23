---
name: create-twig-form-template
description: >
  Create the Twig template for the add/edit form page. Renders the Symfony form
  with NavigationTabType tabs and mounts the Vue.js form manager for dynamic
  sections.
needs: [create-form-type, create-symfony-admin-controller, create-ts-entry-point]
produces: "form.html.twig — add/edit form page template with tab navigation"
---

# create-twig-form-template

## Instructions

1. `{% extends '@PrestaShop/Admin/layout.html.twig' %}`.
2. `{% block content %}`: render `{{ form_start(form) }}`, `{{ form_widget(form) }}`, `{{ form_end(form) }}`.
3. Add save buttons: `<button type="submit" name="_save_and_stay">Save and stay</button>` and `<button type="submit">Save</button>`.
4. Mount Vue app: `<div id="app-{domain}-form"></div>` — matches JS1's mount point.
5. Enqueue the compiled JS asset: `{% block extra_javascript %}{{ asset('js/{domain}.bundle.js') }}{% endblock %}`.
6. For tabs: the NavigationTabType widget renders tab navigation automatically — just call `form_widget(form)`.

## Rules

- Mount point ID must exactly match JS1's `app.mount('#app-{domain}-form')`
- Always include form CSRF token via `form_widget(form._token)` if rendering manually
- Asset path must match the webpack entry name registered in JS1
