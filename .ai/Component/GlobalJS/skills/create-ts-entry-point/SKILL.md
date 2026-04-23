---
name: create-ts-entry-point
description: >
  Create the TypeScript entry point that boots the Vue.js form manager for the
  entity's add/edit form. This file is the Webpack entry and must import and
  mount all Vue components for the domain.
needs: [create-form-type]
produces: "admin-dev/themes/new-theme/js/pages/{domain}/index.ts — TypeScript entry point for the domain form"
---

# create-ts-entry-point

## Instructions

1. Create `admin-dev/themes/new-theme/js/pages/{domain}/index.ts`.
2. Import Vue 3: `import {createApp} from 'vue'`.
3. Import the form manager component: `import {Domain}FormManager from './{Domain}FormManager.vue'`.
4. Mount: `const app = createApp({Domain}FormManager); app.mount('#app-{domain}-form')`.
5. Import the tab error navigator: `import initTabErrorNavigator from './tabErrorNavigator'`.
6. On DOMContentLoaded: `initTabErrorNavigator()`.
7. Register the entry point in `admin-dev/themes/new-theme/webpack.config.js` as a new entry: `'{domain}': './js/pages/{domain}/index.ts'`.

## Rules

- Entry point must be registered in webpack.config.js — untouched entries are not compiled
- Mount point ID must match the Twig template's container element
- Only import — do not inline component logic in the entry point
