---
name: register-behat-context
description: >
  Register the new `{Domain}FeatureContext` in the Behat suite configuration so
  the step definitions are discovered. Without this step, all step definitions
  in the new context class are invisible to Behat.
needs: [create-behat-context-class]
produces: "behat.yml or suite configuration updated with new context class"
---

# register-behat-context

## Instructions

1. Open `tests/Integration/Behaviour/behat.yml`.
2. Find the correct suite (usually `domain` suite).
3. Add the context class: `- PrestaShop\Tests\Integration\Behaviour\Features\Context\Domain\{Domain}FeatureContext`.
4. Verify the feature file path is discoverable by the suite's `paths` configuration.
5. Run `php vendor/bin/behat --dry-run` to confirm all steps are matched.

## Rules

- Add to the correct suite — not the default suite
- Run dry-run before running actual tests to catch unimplemented steps
- Context class FQCN must match the file path exactly (PSR-4)
