---
name: add-legacy-deprecation-notice
description: >
  Add a visible yellow warning banner to the legacy admin controller's `init()`
  method, shown only when the new Symfony page is available. This step happens
  6–12 months after GA, not at GA time.
needs: [promote-feature-flag-to-stable]
produces: "Deprecation warning banner in AdminXxxController::init() visible when legacy page is accessed"
---

# add-legacy-deprecation-notice

## Instructions

1. Confirm at least 6 months have passed since GA, no P1 bugs, stable release confirmed.
2. Open `controllers/admin/Admin{Domain}sController.php`.
3. Locate or create the `init()` method.
4. Add after `parent::init()`:
   ```php
   if ($this->isNewPageAvailable()) {
       $this->warnings[] = $this->trans(
           'This page will be removed in a future version. Please switch to the new %link%{Domain}s management page%/link%.',
           [
               '%link%' => sprintf('<a href="%s">', $this->context->link->getAdminLink('admin_{domain}s_index')),
               '%/link%' => '</a>',
           ],
           'Admin.Notifications.Warning'
       );
   }
   ```
5. Implement `private function isNewPageAvailable(): bool`:
   ```php
   return (bool) \Configuration::get('PS_FEATURE_FLAG_{DOMAIN}');
   ```
6. Or use FeatureFlagRepository if available in the legacy context.

## Rules

- NEVER delete the legacy controller — only add the notice
- Notice goes in `$this->warnings[]` (yellow banner), not `$this->errors[]` (blocking red)
- Notice only shows when the feature flag is ON — never always-on
- Do NOT add `@trigger_error()` PHP deprecation warnings — causes noise in merchant logs
- Do NOT disable or break the legacy controller — it must remain fully functional
