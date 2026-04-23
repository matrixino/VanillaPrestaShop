---
name: create-twig-breadcrumb
description: >
  Documents how to configure the PrestaShop admin breadcrumb in Symfony
  templates. The breadcrumb is populated via the controller's
  `breadcrumbsAndTitle` block or via Twig.
needs: [create-twig-index-template, create-twig-form-template]
produces: "Breadcrumb configuration for index and form pages"
---

# create-twig-breadcrumb

## Instructions

1. In the controller action, call `$this->setBreadcrumbs(['Home', '{Section}', '{Domain}s'])` if that helper exists.
2. Alternatively, in the Twig template: `{% block page_title %}{{ 'Manage {Domain}s'|trans({}, 'Admin.{Section}.Feature') }}{% endblock %}`.
3. For form page: breadcrumb should be `Home > {Section} > {Domain}s > Edit`.
4. Check the PS breadcrumb helper in `FrameworkBundleAdminController` for the correct method name.

## Rules

- Page titles must be translatable
- Breadcrumb chain must reflect the navigation hierarchy
