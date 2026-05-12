# Forms Component

## Purpose

Infrastructure for building, populating, and handling back-office forms. PrestaShop has **two distinct form patterns** that share almost no code: settings forms (config blocks) and CRUD forms (entity add/edit). Picking the wrong pattern is the most common AI mistake — see the decision section below.

## Settings forms vs CRUD forms — which one is this page?

Three yes/no questions, in order:

1. Does the page persist values into `ps_configuration` (or another flat key/value store)? → **settings form**.
2. Is the data identified by an entity ID and listed in a grid for create/edit/delete? → **CRUD form** (a.k.a. *identifiable* form).
3. Is there (or should there be) a CQRS `Add{Domain}Command` / `Edit{Domain}Command` for it? → **CRUD form**. Otherwise → **settings form**.

A single admin page may carry **both** patterns side-by-side (e.g. *Shop parameters > Contact > Stores* has a CRUD grid for store entities **and** a settings form for global contact details). Treat each block independently.

| Pattern | Use case | Base handler | Skill |
|---|---|---|---|
| Settings form | options block, page-level configuration, `fields_options` migration | `PrestaShop\PrestaShop\Core\Form\Handler` (registered as service, never subclassed) | [`create-settings-form`](skills/create-settings-form/SKILL.md) |
| CRUD form | entity add/edit (with grid listing) | `IdentifiableObject\Builder\FormBuilder` + `IdentifiableObject\Handler\FormHandler` (factory-built, never subclassed) | [`create-crud-form-type`](skills/create-crud-form-type/SKILL.md) + [`create-crud-form-data-handling`](skills/create-crud-form-data-handling/SKILL.md) |

## Settings form pattern

### Required layers

| Layer | Path | Role |
|---|---|---|
| DataConfiguration | `src/Adapter/{Domain}/{Name}Configuration.php` | extends `AbstractMultistoreConfiguration`. Reads/writes config rows. Implements `getConfiguration(): array`, `updateConfiguration(array): array` (returns array of errors), and protected `buildResolver(): OptionsResolver` for option validation. |
| FormDataProvider | `src/PrestaShopBundle/Form/Admin/{Section}/{Name}FormDataProvider.php` | implements `PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface`. Two-line bridge: `getData()` → `dataConfiguration->getConfiguration()`, `setData($data)` → `dataConfiguration->updateConfiguration($data)`. |
| FormType | `src/PrestaShopBundle/Form/Admin/{Section}/{Name}Type.php` | standard `AbstractType` / `TranslatorAwareType`. **This is the root form** — no `getParent()`, no wrapper, no entity binding. |

### Service definitions (4 YAML entries, no PHP handler class)

| Service | YAML file | Class |
|---|---|---|
| DataConfiguration | `src/PrestaShopBundle/Resources/config/services/adapter/data_configuration.yml` | your `{Name}Configuration` |
| FormDataProvider | `src/PrestaShopBundle/Resources/config/services/bundle/form/form_data_provider.yml` | your `{Name}FormDataProvider` |
| FormHandler | `src/PrestaShopBundle/Resources/config/services/bundle/form/form_handler.yml` | **`PrestaShop\PrestaShop\Core\Form\Handler`** (the base class — never your own) |
| FormType | `src/PrestaShopBundle/Resources/config/services/bundle/form/form_type.yml` | empty entry for auto-discovery |

The `Handler` service takes 6 ordered arguments: form factory, hook dispatcher, your FormDataProvider, FormType FQCN, **hook name** (PascalCase, e.g. `CountriesPageOptions`), **form name** (kebab-case, e.g. `country-options`). The hook name drives the two module-facing hooks below.

### Hooks dispatched by the base `Handler`

| Hook | When | Parameters | Use |
|---|---|---|---|
| `action{HookName}Form` | inside `Handler::getForm()` | `form_builder` (mutable reference) | modules add/remove/modify fields before render |
| `action{HookName}Save` | inside `Handler::save()` | `errors` and `form_data` (both passed by reference) | modules inject validation errors or rewrite data before persistence |

These hooks are how external modules extend back-office settings forms. Reimplementing `FormHandlerInterface` from scratch silently removes both hooks — see the "Custom `FormHandlerInterface`" anti-pattern below.

### Allowed exception — extending `Handler` for save-time side effects

A handful of pages need a side effect at save time that does not belong in `DataConfiguration` (cache invalidation, cross-field coercion): `ProductPreferencesFormHandler`, `PreferencesFormHandler`, `CustomerPreferencesFormHandler`, `TranslationsSettingsFormHandler`, `InvoiceByDateFormHandler`, `InvoiceByStatusFormHandler`, `ImportFormHandler`. Rule:

- The class must **`extends Handler`** — never reimplement `FormHandlerInterface` from scratch.
- Overridden `save()` must call **`parent::save()`** so the hook dispatch still fires.
- Cache-clearing or similar plumbing dependencies are wired via service `calls:` in the YAML.

### Settings form anti-patterns (named symptoms)

**"Outer wrapper builder"** — symptom: a visible wrapper label, a `label => false` workaround on the only `add()` call, submitted data shaped like `['xxx' => [...real fields...]]`, the FormType ends up nested as a child instead of being the root form.

```php
// ❌ WRONG — this is what PR #41414 generated:
$this->formFactory->createBuilder()
    ->add('contact_details', ContactDetailsType::class, ['label' => false])
    ->setData(['contact_details' => $this->dataProvider->getData()])
    ->getForm();
```

The correct construction is `Handler::getForm()`'s one-liner (see `src/Core/Form/Handler.php:83`):

```php
$formBuilder = $this->formFactory->createNamedBuilder($this->formName, $this->form);
```

The FormType IS the root form — there is never an extra outer builder.

**"Custom `FormHandlerInterface` implementation"** — symptom: a file like `XxxFormHandler.php` implementing the interface from scratch, with its own `getForm()`/`save()` body. The module hooks `action{HookName}Form` (dispatched at `Handler.php:87`) and `action{HookName}Save` (dispatched at `Handler.php:107`) never fire. Modules silently lose the ability to extend the form. Fix: delete the class, register a `PrestaShop\PrestaShop\Core\Form\Handler` service in `form_handler.yml` instead.

### Canonical example

PR #41406 — "Migrate country options" — adds a textbook settings-form: `src/Adapter/Country/CountryOptionsConfiguration.php`, `src/PrestaShopBundle/Form/Admin/Improve/International/Locations/CountryOptionsFormDataProvider.php`, `CountryOptionsType.php`, and four YAML entries. Read these files when in doubt.

## CRUD form pattern

### Required layers

| Layer | Path | Role |
|---|---|---|
| FormDataProvider | `src/Core/Form/IdentifiableObject/DataProvider/{Domain}FormDataProvider.php` | `getData($id): array` dispatches `Get{Domain}ForEditing`; `getDefaultData(): array` returns create-form defaults |
| FormDataHandler | `src/Core/Form/IdentifiableObject/DataHandler/{Domain}FormDataHandler.php` | `create(array $data): mixed` dispatches `Add{Domain}Command`; `update($id, array $data): void` dispatches `Edit{Domain}Command` |
| FormType | `src/PrestaShopBundle/Form/Admin/{Section}/{Domain}/{Domain}Type.php` | fields, validation constraints. No CQRS knowledge. |
| Base FormBuilder | `PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Builder\FormBuilder` | **factory-built service — never subclassed**. Controller calls `getForm()` / `getFormFor($id)`. |
| Base FormHandler | `PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Handler\FormHandler` | **factory-built service — never subclassed**. Controller calls `handle($form)` / `handleFor($id, $form)`. |

### Service definitions

| Service | YAML file |
|---|---|
| FormType | `src/PrestaShopBundle/Resources/config/services/core/form/form_type.yml` (or `bundle/form/form_type.yml`) |
| FormDataProvider | `src/PrestaShopBundle/Resources/config/services/core/form/form_data_provider.yml` |
| FormDataHandler | `src/PrestaShopBundle/Resources/config/services/core/form/form_data_handler.yml` |
| FormBuilder | `src/PrestaShopBundle/Resources/config/services/core/form/form_builder.yml` (uses `prestashop.core.form.builder.form_builder_factory` factory) |
| FormHandler | `src/PrestaShopBundle/Resources/config/services/core/form/form_handler.yml` (uses `prestashop.core.form.identifiable_object.handler.form_handler_factory` factory) |

Service-ID convention: `prestashop.core.form.identifiable_object.{builder|handler|data_provider|data_handler}.{domain}_form_{role}`.

### Hooks dispatched by the IdentifiableObject `FormHandler`

- `actionBeforeCreate{FormName}FormHandler`
- `actionAfterCreate{FormName}FormHandler`
- `actionBeforeUpdate{FormName}FormHandler`
- `actionAfterUpdate{FormName}FormHandler`
- plus the `FormBuilderModifier` extension point for in-flight builder mutation by modules

### CRUD form anti-pattern

**"Custom FormBuilder or FormHandler class"** — symptom: a hand-rolled class extending the IdentifiableObject base. There is no legitimate use case; the factory-built services support every pattern needed (file uploads, multilingual, tabs, sub-resource commands). If a hand-rolled class appears in a PR, it is a sign the AI invented one — delete it and use the factory services.

## Critical YAML folder distinction

`src/PrestaShopBundle/Resources/config/services/bundle/form/` = **settings forms** (use the base `Handler` class).
`src/PrestaShopBundle/Resources/config/services/core/form/` = **CRUD forms** (use the IdentifiableObject factories).

Easy to mix up. Wrong folder will not raise a hard error — the service simply won't be picked up by the auto-discovery, and the page will fail at runtime with a "service not found" message.

## Shared concerns (both patterns)

| Topic | Note |
|---|---|
| Translatable fields | `TranslatableType` wrapping the inner type. Data is an array keyed by language ID. Multilingual textareas wrap `TextareaType` / `FormattedTextareaType`. |
| Money / decimal | `MoneyType` for static currency, `AmountType` for multi-currency. PrestaShop stores prices with 6 decimal places — always set explicit decimal scale, never round to float. Commands carry `DecimalNumber`, never native `float`. |
| File uploads | `FileType` with `'mapped' => false, 'required' => false`. Actual file saving happens in the DataHandler (CRUD) or DataConfiguration (settings), not the form type. |
| Choice providers | `ChoiceProviderInterface` services injected into form types for dynamic select options. `FormOptionsProviderInterface` for options evaluated at render time (e.g. carrier lists). |
| Form extensions | `src/PrestaShopBundle/Form/Extension/` adds custom options (e.g. `external_link`, `modify_all_shops`) to all form types globally. Check existing extensions before adding new options. |
| Tab layout | `NavigationTabType` is a generic Symfony composition pattern — applicable in either pattern but used today almost exclusively by complex CRUD forms (Carrier, Product). See [`create-form-tab-layout`](skills/create-form-tab-layout/SKILL.md). |
| Form utilities | `FormBuilderModifier`, `FormCloner`, `FormHelper` under `src/PrestaShopBundle/Form/` — tools for mutating form builders at runtime (mostly module use). |
| Choice providers location | `src/Core/Form/ChoiceProvider/` (61+) + `src/Adapter/Form/ChoiceProvider/` (26) |

## Canonical references

| File | Pattern | Use case |
|---|---|---|
| `src/Adapter/Country/CountryOptionsConfiguration.php` | settings | minimal `AbstractMultistoreConfiguration` extension |
| `src/PrestaShopBundle/Form/Admin/Improve/International/Locations/CountryOptionsFormDataProvider.php` | settings | minimal `FormDataProviderInterface` bridge |
| `src/PrestaShopBundle/Form/Admin/Configure/ShopParameters/General/MaintenanceType.php` | settings | typical settings FormType with several fields |
| `src/Core/Form/IdentifiableObject/DataHandler/TaxFormDataHandler.php` | CRUD | simple DataHandler |
| `src/Core/Form/IdentifiableObject/DataHandler/ProductFormDataHandler.php` | CRUD | complex DataHandler with `CommandBuilder` delegation |
| `src/Core/Form/Handler.php` | settings (base class) | source-of-truth for the hook-dispatch contract |

## Skills

| Skill | Trigger |
|-------|---------|
| [`create-settings-form`](skills/create-settings-form/SKILL.md) | "create settings form for {Page}" |
| [`create-crud-form-type`](skills/create-crud-form-type/SKILL.md) | "create CRUD form type for {Domain}" |
| [`create-crud-form-data-handling`](skills/create-crud-form-data-handling/SKILL.md) | "create CRUD form data handling for {Domain}" |
| [`create-form-tab-layout`](skills/create-form-tab-layout/SKILL.md) | "create tab layout for {Domain} form" |
