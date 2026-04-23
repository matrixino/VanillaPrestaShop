---
name: create-choice-provider
description: >
  Create a ChoiceProvider service that fetches options for a `ChoiceType` form
  field from the database. Used when select options are dynamic (e.g., list of
  tax rule groups, carrier zones).
needs: [create-form-type]
produces: "{Domain}{Field}ChoiceProvider.php — service providing choices for ChoiceType fields"
conditional: "only for fields rendered as select/radio with dynamic choices from DB"
---

# create-choice-provider

## Instructions

1. Create `{Domain}{Field}ChoiceProvider.php` implementing `ChoiceProviderInterface` (if it exists) or as a plain service.
2. Inject the repository or DBAL connection.
3. `getChoices(): array` — return `['Label' => value]` array.
4. Inject into the form type and pass as `choices` option to `ChoiceType`.
5. Consider caching if the list is expensive to compute.

## Rules

- Choice keys are display labels, values are the actual DB values (int IDs, etc.)
- ChoiceProvider must return stable, sorted choices — UI must be deterministic
