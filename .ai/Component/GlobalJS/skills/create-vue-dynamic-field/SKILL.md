---
name: create-vue-dynamic-field
description: >
  Create a Vue SFC for a single complex field or field group that requires
  reactivity — such as a dynamic price range table where rows can be
  added/removed, or a conditional field that appears based on another field's
  value.
needs: [create-vue-form-manager, create-form-type]
produces: "Vue SFC for a single dynamic field or field group (e.g., price ranges, zone multiselect)"
conditional: "only for fields with dynamic rows, conditional visibility, or live calculation"
---

# create-vue-dynamic-field

## Instructions

1. Props: `modelValue` of the appropriate type (array for multi-row, string/number for single).
2. Emit: `update:modelValue` for v-model compatibility.
3. For multi-row (e.g., ranges): render a `<table>` with a row per item, "Add row" button, and "Remove" per row.
4. For conditional visibility: `v-if="otherField === 'value'"`.
5. Sync final value to hidden input for Symfony.

## Rules

- Use v-model pattern (modelValue prop + update:modelValue emit) for composability
- Rows added at runtime must be immediately reflected in the hidden input's value
