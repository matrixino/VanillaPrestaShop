---
name: create-vue-tab-component
description: >
  Create a Vue SFC for a form tab that has dynamic behavior (e.g.,
  showing/hiding fields based on a toggle, dynamic table rows). Static tabs
  rendered entirely by Twig do not need Vue components.
needs: [create-vue-form-manager, create-form-tab-layout]
produces: "Vue SFC for a dynamic form tab section"
conditional: "only for tabs with dynamic/interactive content"
---

# create-vue-tab-component

## Instructions

1. Define `defineProps<{ propName: Type }>()` for all received data.
2. Define `defineEmits<{ (e: 'update:propName', value: Type): void }>()` for updates.
3. Template: render the dynamic section (e.g., a dynamic table of ranges, a conditional field group).
4. On user input: `emit('update:propName', newValue)`.
5. Use `v-model` on child inputs where possible.

## Rules

- Tab component is only created if the tab has JS-driven dynamic behavior
- Static-only tabs are handled purely in Twig — no Vue component needed
