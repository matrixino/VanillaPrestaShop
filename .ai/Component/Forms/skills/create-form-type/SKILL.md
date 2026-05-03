---
name: create-form-type
description: >
  Create the Symfony form type for an entity's add/edit form. Covers standard field
  types, translatable fields, money fields, file uploads, and choice providers.
  For multi-tab layout with NavigationTabType, see create-form-tab-layout.
  Trigger: "create form type for {Domain}".
needs: [create-cqrs-commands, create-cqrs-queries]
produces: "{Domain}Type.php + choice providers — Symfony form structure for add/edit"
subagent: optional
---

# create-form-type

Read `@.ai/Component/Forms/CONTEXT.md` for form conventions (base classes, data flow, service registration).

## 1. Root form type

Create `src/PrestaShopBundle/Form/Admin/{Section}/{Domain}/{Domain}Type.php`:

- Extend `TranslatorAwareType` (provides `$this->trans()`) or `AbstractType` for simple forms
- `buildForm()`: add all fields for the entity
- `configureOptions()`: set defaults as needed
- Form types define structure and validation only — no knowledge of commands/queries

**Reference:** `src/PrestaShopBundle/Form/Admin/Improve/International/Tax/TaxType.php` (simple), `src/PrestaShopBundle/Form/Admin/Sell/Catalog/Manufacturer/ManufacturerType.php` (with image)

## 2. Standard field types

| PS field concept | Symfony/PS type | Notes |
|---|---|---|
| Text | `TextType` | Standard input |
| Boolean toggle | `SwitchType` (PS-specific) | On/off switch |
| Select with static options | `ChoiceType` | Inline choices array |
| Select with dynamic options | `ChoiceType` + `ChoiceProvider` | See section 5 |
| Textarea / HTML | `TextareaType` or `FormattedTextareaType` | |

## 3. Translatable fields

For multilingual fields (entity has `_lang` table):

```php
->add('name', TranslatableType::class, [
    'type' => TextType::class,
    'options' => ['constraints' => [new NotBlank()]],
])
```

- `TranslatableType` renders one input per active shop language
- Submitted data: `['name' => [1 => 'English', 2 => 'French']]`
- Map to command's `setLocalizedNames()` setter in the DataHandler

For translatable textareas: wrap `TextareaType` or `FormattedTextareaType`.

## 4. Money / price fields

For monetary fields (see [Forms/CONTEXT.md](../../CONTEXT.md) for decimal scale convention):

- Static currency: `MoneyType::class` with `'currency' => $defaultCurrencyIsoCode`
- Multi-currency: PS-specific `AmountType` if available
- Use appropriate transformers to convert between form display and storage

## 5. Choice providers

For select fields with dynamic options from DB:

- Create `{Domain}{Field}ChoiceProvider.php` implementing `ChoiceProviderInterface`
- Inject repository or DBAL connection
- `getChoices(): array` — return `['Label' => value]` array
- Inject into the form type and pass as `choices` option

**Reference:** `src/Core/Form/ChoiceProvider/` (61+ existing providers)

## 6. File upload fields

For image/logo uploads (see [Forms/CONTEXT.md](../../CONTEXT.md) for file upload conventions):

- Add `FileType::class` with `'mapped' => false, 'required' => false`
- Add `File` constraint with allowed MIME types
- Display existing image in the edit template via custom Twig block

## Rules

Conventions (base classes, file uploads, choice providers, NavigationTabType) are in [Forms/CONTEXT.md](../../CONTEXT.md). Skill-specific reminders:

- Add Symfony validation constraints directly on form fields (`NotBlank`, `Length`, `Regex`)
- For multi-tab layout, use the `create-form-tab-layout` skill instead
