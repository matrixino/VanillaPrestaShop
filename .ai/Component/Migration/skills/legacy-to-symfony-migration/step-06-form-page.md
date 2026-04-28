---
step: 6
title: "Form page (vertical slice)"
previous: step-05-listing-page.md
next: step-07-playwright-tests.md
deliverable: "A working add/edit form: form type + data handling + controller form actions + form routes + form template + form JS, all reachable via the feature flag"
---

# Step 6 — Form page (vertical slice)

This step produces a complete, demoable add/edit page in one go. Like the listing slice (step 5), every layer needed for the form — type, data flow, controller, routing, template, JS — is built and wired here.

Read `@.ai/Component/Forms/CONTEXT.md`, `@.ai/Component/Controller/CONTEXT.md`, `@.ai/Component/Twig/CONTEXT.md`, `@.ai/Component/Javascript/CONTEXT.md` for the conventions of each layer.

## Slice ordering reminder

If listing (step 5) ran first, the controller class and routing YAML already exist — this slice **extends** them with form actions and form routes. If this slice runs first (rare), it creates the controller class and routing YAML; step 5 then extends them later. Both invocations of `create-controller-*` and `create-admin-routing` are designed to support either case.

## Skills to invoke

In suggested order:

| Skill | Produces |
|---|---|
| `create-form-type` | `{Domain}Type` extending `TranslatorAwareType`, with each field mapped from the manifest |
| `create-form-tab-layout` | `NavigationTabType`-based tab structure — **conditional, complex pages only**. Most pages do not use tabs. |
| `create-form-data-handling` | `DataProvider` (loads `Editable{Domain}` for edit) + `DataHandler` (dispatches Add/Edit + sub-resource commands) + DI registration |
| `create-controller-form-actions` | `createAction`, `editAction` using `FormBuilder` + `FormHandler` injected as action arguments. Creates the controller class on first slice, extends it on second. |
| `create-admin-routing` | Form routes (`create`, `edit`, plus any custom action). Carries `_legacy_feature_flag: {domain}` on each route. Creates YAML on first slice, extends on second. |
| `create-twig-form-template` | `form.html.twig` and any form theme overrides |
| `create-ts-entry-point` | `js/pages/{domain}/form.ts` + webpack entry for the form |
| `init-js-components` | `initComponents()` calls for translatable inputs, choice trees, TinyMCE editors, etc. — driven by `data-*` attributes in the form template |
| `create-vue-component` | Vue SFC for sections that need rich interactivity beyond standard JS components — **exception only**. Most pages do not need Vue. |

## Orchestration notes

- `FormBuilder` and `FormHandler` are injected as **action arguments** (Symfony argument resolver), not in the constructor. This is the preferred order across PrestaShop controllers; reserve `getSubscribedServices` for services shared across many actions, and constructor injection only when neither fits.
- The `DataProvider`/`DataHandler` pair is the standard for forms whose data round-trips through CQRS commands. For one-off actions that do not fit this pattern, dispatching a command directly from the controller is acceptable — the pattern is not mandatory for every action.
- Sub-resource commands are dispatched separately by the `DataHandler` after the main Add/Edit command — never inline them in `EditXxxCommand`.
- Form theme overrides may be declared either via `{% form_theme %}` in the Twig file or via the PrestaShop-specific `'form_theme'` form option (preferred today). Pick one location per form.
- Tab layout is the exception: only invoke `create-form-tab-layout` when the manifest's complexity decision called for it. The default form is single-column, no tabs.

## Gate to next step

- [ ] With flag enabled, the create page renders and saves
- [ ] With flag enabled, the edit page renders, prefills, and saves
- [ ] Validation errors map to translatable flash messages
- [ ] Sub-resource updates (if any) persist through the appropriate sub-resource commands
- [ ] File uploads (if any) work and survive a re-edit
- [ ] With flag disabled, the legacy form still loads (no regression)
