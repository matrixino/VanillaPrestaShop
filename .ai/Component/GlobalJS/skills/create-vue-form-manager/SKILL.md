---
name: create-vue-form-manager
description: >
  Create the root Vue SFC that manages the overall form state and coordinates
  between sub-components. Holds the reactive state shared across tabs and emits
  form data to hidden inputs for Symfony form submission.
needs: [create-ts-entry-point, create-form-type]
produces: "{Domain}FormManager.vue — top-level Vue component orchestrating the form"
---

# create-vue-form-manager

## Instructions

1. Create `{Domain}FormManager.vue` with `<template>`, `<script setup lang="ts">`, `<style scoped>`.
2. Import child components (tab components, dynamic field components).
3. Define reactive state with `ref()` or `reactive()` for each dynamic form section.
4. For each Vue-managed field, use `watch()` to sync state to a hidden `<input type="hidden">` that Symfony form expects.
5. Pass state as props to child components via `:propName="state.field"`.
6. Listen to child `@update` events to mutate parent state.

## Rules

- State flows down as props, updates flow up as events (standard Vue pattern)
- Hidden input names must exactly match Symfony form field names
- Do not mix Vue reactivity with jQuery DOM manipulation
