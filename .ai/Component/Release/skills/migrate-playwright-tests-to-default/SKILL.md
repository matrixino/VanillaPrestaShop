---
name: migrate-playwright-tests-to-default
description: >
  After GA, the new pages are the default and the feature flag is no longer
  needed in tests. Edit all campaigns to remove the `enableFeatureFlag` call
  from `beforeAll` and update any legacy URLs to use Symfony routes.
needs: [promote-feature-flag-to-stable, create-playwright-crud-campaign, create-playwright-filter-campaign, create-playwright-bulk-campaign, create-playwright-position-campaign, create-playwright-tab-campaign]
produces: "All E3–E7 campaign files edited to remove feature flag enable call and use Symfony URLs"
---

# migrate-playwright-tests-to-default

## Instructions

1. For each campaign file (E3–E7):
   a. Remove: `await testContext.enableFeatureFlag('{domain}')` from `beforeAll`.
   b. Replace any `index.php?controller=Admin{Domain}s` URLs with Symfony route paths.
   c. Update any page object imports if the HTML structure changed between legacy and new pages.
2. Run the full campaign suite without the flag enable call.
3. All campaigns must pass — if any fail, fix the URL or selector mismatch before merging.
4. This edit is done in the SAME PR as R1 — or immediately after, in the same sprint.

## Rules

- Remove the enableFeatureFlag call completely — do not leave it commented out
- Test suite must remain green after removing the call
- If a campaign tests legacy-to-new redirect behavior, keep it as a separate campaign (do not delete it)
