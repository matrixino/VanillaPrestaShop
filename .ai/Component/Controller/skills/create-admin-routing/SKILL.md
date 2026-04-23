---
name: create-admin-routing
description: >
  Create the Symfony routing YAML file declaring all admin routes for the
  domain. Every route must carry `_legacy_feature_flag` and `_legacy_controller`
  to enable feature-flag-based routing between legacy and new controller.
needs: [create-symfony-admin-controller]
produces: "YAML routing file with all admin routes carrying _legacy_feature_flag and _legacy_controller"
---

# create-admin-routing

## Instructions

1. Create `src/PrestaShopBundle/Resources/config/routing/admin/{domain}.yml`.
2. For each action in H1, declare a route:
   ```yaml
   admin_{domain}s_index:
     path: /{domain}s
     methods: [GET]
     defaults:
       _controller: 'PrestaShopBundle:Admin/{Section}/{Domain}:index'
       _legacy_controller: Admin{Domain}s
       _legacy_feature_flag: {domain}
   ```
3. Include routes: index (GET), create (GET+POST), edit (GET+POST with `{id}` parameter), delete (POST with `{id}`), toggle status (POST+JSON), bulk delete/enable/disable (POST).
4. Import this file from the main admin routing file.
5. CRITICAL: `_legacy_feature_flag` value must exactly match the `id` attribute in feature_flag.xml.
6. Verify with `php bin/console debug:router | grep {domain}`.

## Rules

- H1 + H2 + H3 MUST be committed together — routes referencing an unregistered flag cause a 500
- _legacy_feature_flag value is case-sensitive and must match H3 exactly
- Edit and delete routes must include `{id}` path parameter
- Toggle status route must allow POST method and return JSON
