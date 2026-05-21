# Forms Component

## Purpose

Infrastructure for building, populating, and handling back-office forms. PrestaShop has **two distinct form patterns** that share almost no code: settings forms (config blocks) and CRUD forms (entity add/edit). Picking the wrong pattern is the most common AI mistake — see the decision section below.

Pattern-specific rules live in companion files. Read them on demand, not preemptively:
- **Settings forms:** [`SETTINGS.md`](SETTINGS.md) — base `Handler`, hooks, anti-patterns, allowed exception, YAML folder, canonical example.
- **CRUD forms:** [`CRUD.md`](CRUD.md) — base FormBuilder/FormHandler factories, hooks, anti-pattern, YAML folder.

## Settings forms vs CRUD forms — which one is this page?

Three yes/no questions, in order:

1. Does the page persist values into `ps_configuration` (or another flat key/value store)? → **settings form**.
2. Is the data identified by an entity ID and listed in a grid for create/edit/delete? → **CRUD form** (a.k.a. *identifiable* form).
3. Is there (or should there be) a CQRS `Add{Domain}Command` / `Edit{Domain}Command` for it? → **CRUD form**. Otherwise → **settings form**.

A single admin page may carry **both** patterns side-by-side (e.g. *Shop parameters > Contact > Stores* has a CRUD grid for store entities **and** a settings form for global contact details). Treat each block independently.

| Pattern | Use case | Read | Skill |
|---|---|---|---|
| Settings form | options block, page-level configuration, `fields_options` migration | [`SETTINGS.md`](SETTINGS.md) | [`create-settings-form`](skills/create-settings-form/SKILL.md) |
| CRUD form | entity add/edit (with grid listing) | [`CRUD.md`](CRUD.md) | [`create-crud-form-type`](skills/create-crud-form-type/SKILL.md) + [`create-crud-form-data-handling`](skills/create-crud-form-data-handling/SKILL.md) |

## Critical YAML folder distinction

The two patterns use **different** service-definition folders:

- `src/PrestaShopBundle/Resources/config/services/bundle/form/` → **settings forms** (base `Handler`)
- `src/PrestaShopBundle/Resources/config/services/core/form/` → **CRUD forms** (IdentifiableObject factories)

Easy to mix up. Wrong folder won't raise a hard error — the service simply isn't picked up by auto-discovery, and the page fails at runtime with "service not found". Details in each pattern's companion file.

## Shared concerns (both patterns)

| Topic | Note |
|---|---|
| Reusable form types | **`PrestaShopBundle\Form\Admin\Type\`** holds 80+ generic, PrestaShop-specific form types (`SwitchType`, `TranslatableType`, `MoneyType`, `CountryChoiceType`, `CurrencyChoiceType`, `ColorPickerType`, `IpAddressType`, `EmailType`, `MaterialChoiceTreeType`, etc., plus the `Common/` and `Material/` sub-namespaces). **Check this namespace before reinventing a field** — there is almost always a ready-made type. |
| Form extensions | **`PrestaShopBundle\Form\Extension\`** holds 24+ Symfony `AbstractTypeExtension` classes that add custom options to all form types globally: `help`, `hint`, `external_link`, `modify_all_shops`, `autocomplete`, `multistore_configuration_key`, `download_file`, `disabling_switch`, etc. **Check this namespace before inventing a new field option** — the option you want probably already exists. |
| Translatable fields | `TranslatableType` (in `Form\Admin\Type\`) wrapping the inner type. Data is an array keyed by language ID. Multilingual textareas wrap `TextareaType` / `FormattedTextareaType`. |
| Money / decimal | `MoneyType` for static currency, `AmountType` for multi-currency. PrestaShop stores prices with 6 decimal places — always set explicit decimal scale, never round to float. Commands carry `DecimalNumber`, never native `float`. |
| File uploads | `FileType` with `'mapped' => false, 'required' => false`. Actual file saving happens in the DataHandler (CRUD) or DataConfiguration (settings), not the form type. |
| Choice providers | `ChoiceProviderInterface` services injected into form types for dynamic select options. `FormOptionsProviderInterface` for options evaluated at render time (e.g. carrier lists). Located under `src/Core/Form/ChoiceProvider/` (61+) + `src/Adapter/Form/ChoiceProvider/` (26). |
| Tab layout | `NavigationTabType` is a generic Symfony composition pattern — applicable in either pattern but used today almost exclusively by complex CRUD forms (Carrier, Product). See [`create-form-tab-layout`](skills/create-form-tab-layout/SKILL.md). |
| Form utilities | `FormBuilderModifier`, `FormCloner`, `FormHelper` under `src/PrestaShopBundle/Form/` — tools for mutating form builders at runtime (mostly module use). |

## Skills

| Skill | Trigger | Pattern detail to load |
|-------|---------|------------------------|
| [`create-settings-form`](skills/create-settings-form/SKILL.md) | "create settings form for {Page}" | [`SETTINGS.md`](SETTINGS.md) |
| [`create-crud-form-type`](skills/create-crud-form-type/SKILL.md) | "create CRUD form type for {Domain}" | [`CRUD.md`](CRUD.md) |
| [`create-crud-form-data-handling`](skills/create-crud-form-data-handling/SKILL.md) | "create CRUD form data handling for {Domain}" | [`CRUD.md`](CRUD.md) |
| [`create-form-tab-layout`](skills/create-form-tab-layout/SKILL.md) | "create tab layout for {Domain} form" | — (generic) |
