---
name: promote-feature-flag-to-stable
description: >
  Promote the domain's feature flag from `beta/state=0` to `stable/state=1` in a
  dedicated GA PR. This is the only change in the GA PR — no features, no other
  file changes.
needs: [register-feature-flag, create-playwright-crud-campaign, create-playwright-filter-campaign, create-playwright-bulk-campaign, create-playwright-position-campaign, create-playwright-tab-campaign]
produces: "feature_flag.xml updated to stability=stable and state=1"
---

# promote-feature-flag-to-stable

## Instructions

1. Verify GA prerequisites before opening PR (see step-12-general-availability.md checklist).
2. Open `install-dev/data/xml/feature_flag.xml`.
3. Find `<feature_flag id="{domain}">`.
4. Change `<stability>beta</stability>` to `<stability>stable</stability>`.
5. Change `<state>0</state>` to `<state>1</state>`.
6. Save — these are the ONLY changes in this PR.
7. After merging: manually verify the flag is `stable/state=1` in a fresh install.

## Rules

- GA PR contains ONLY the flag promotion — zero feature changes
- Do not change any other flag entry in this file
- Promote in a dedicated PR so it can be reverted instantly if a critical bug is found
- `stability=stable` + `state=1` together: stable means "shown as stable in admin panel", state=1 means "enabled by default"
