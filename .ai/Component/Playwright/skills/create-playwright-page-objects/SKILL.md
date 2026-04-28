---
name: create-playwright-page-objects
description: >
  Create BO page object classes in the ui-testing-library for a new migrated page.
  Follows the Page Object Model pattern: encapsulate selectors and interactions,
  never assert. Trigger: "create page objects for {Domain}".
needs: [create-controller-listing, create-controller-form-actions, create-admin-routing]
produces: "Page object classes in ui-testing-library: bo{Domain}Page + bo{Domain}CreatePage"
---

# create-playwright-page-objects

Read `@.ai/Component/Playwright/CONTEXT.md` for the POM pattern and ui-testing-library architecture.

Page objects live in the **[ui-testing-library](https://github.com/PrestaShop/ui-testing-library)**, not in the core repo.

## 1. Listing page object

Create `bo{Domain}Page` (extends the appropriate BO base page):

- **Selectors:** grid table, filter inputs, bulk action checkboxes, row action buttons, toggle switches, pagination
- **Methods:** all return values, never assert
  - `getNumberOfElementInGrid(page): Promise<number>`
  - `filterTable(page, filterBy, value): Promise<void>`
  - `resetAndGetNumberOfLines(page): Promise<number>`
  - `getTextColumn(page, row, column): Promise<string>`
  - `goToAddNewPage(page): Promise<void>`
  - `goToEditPage(page, row): Promise<void>`
  - `deleteRow(page, row): Promise<string>` — returns alert text
  - `bulkAction(page, action): Promise<string>` — returns alert text
  - `getToggleColumnValue(page, row): Promise<boolean>`
  - `setToggleColumnValue(page, row, value): Promise<string>`

**Selector naming:** `{name}{Type}` camelCase — e.g. `gridTable`, `filterNameInput`, `bulkActionDropdownButton`, `confirmDeleteButton`

## 2. Create/Edit page object

Create `bo{Domain}CreatePage` (extends BO base page):

- **Selectors:** form inputs, dropdowns, file uploads, save button, tab navigation (if tabs)
- **Methods:**
  - `createEdit{Domain}(page, data): Promise<string>` — fills all fields, clicks save, returns alert text
  - `getPageTitle(page): Promise<string>`
  - `getValue(page, inputName): Promise<string>` — for verification after reload
- **Properties:**
  - `pageTitleCreate: string` — expected page title for create mode
  - `pageTitleEdit: string` — expected page title for edit mode
  - `successfulCreationMessage: string`
  - `successfulUpdateMessage: string`

## 3. Conventions

See [Playwright/CONTEXT.md](../../CONTEXT.md) for POM conventions (never assert, selector naming, inheritance, library location). Skill-specific reminders:

- One page object per distinct BO page (listing page, create/edit page)
- Prefer `data-test` attribute selectors when available over CSS classes
- Check if page objects already exist before creating new ones

**Reference:** check existing page objects in the ui-testing-library for the exact inheritance pattern and method signatures.
