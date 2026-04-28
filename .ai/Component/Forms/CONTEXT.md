# Forms Component

## Purpose

Infrastructure for building, populating, and handling back-office forms tied to identifiable entities: data providers, data handlers, command builders, and choice providers. Does not contain Symfony form type definitions — those live in `src/PrestaShopBundle/Form/Admin/`.

## Layers

| Layer | Path |
|-------|------|
| Core contracts (configuration/settings forms) | `src/Core/Form/FormHandlerInterface.php`, `FormDataProviderInterface.php` |
| IdentifiableObject sub-layer (entity forms) | `src/Core/Form/IdentifiableObject/` |
| CommandBuilder sub-layer | `src/Core/Form/IdentifiableObject/CommandBuilder/` |
| Choice providers (Core, 61+) | `src/Core/Form/ChoiceProvider/` |
| Choice providers (Adapter, 26) | `src/Adapter/Form/ChoiceProvider/` |
| Symfony form types | `src/PrestaShopBundle/Form/Admin/` |
| Form extensions | `src/PrestaShopBundle/Form/Extension/` — add custom options (e.g. `external_link`, `modify_all_shops`) to all form types globally |
| Form utilities | `src/PrestaShopBundle/Form/FormBuilderModifier.php`, `FormCloner.php`, `FormHelper.php` — tools for modifying and cloning form builders at runtime |

## Non-obvious patterns

- Two distinct patterns coexist: configuration/settings `FormHandlerInterface` (settings pages, no `CommandBus`) and modern `IdentifiableObject` layer (entity forms, dispatches CQRS commands)
- Settings pages typically have a pair: one `FormDataProviderInterface` (reads/writes configuration values) + one `FormHandlerInterface` (builds the form and delegates save to the provider). The final `FormHandler` wrapper orchestrates them — you implement the interfaces, not the wrapper.
- `CommandBuilder` bridges raw form `array` → typed CQRS commands; Product domain has 16 builders, Combination has 6 — one per form section, not one per entity
- `FormDataHandlerInterface` has two methods: `create(array $data)` and `update($id, array $data)` — both return the entity ID
- `FormOptionsProviderInterface` supplies **dynamic** form options (carriers, tax rules) evaluated at render time, distinct from static choice providers
- Form extensions in `src/PrestaShopBundle/Form/Extension/` add custom options to all form types globally — check existing extensions before adding new form options

## Canonical examples

- `src/Core/Form/IdentifiableObject/DataHandler/TaxFormDataHandler.php` — simple entity form data handler (typical use case)
- `src/Core/Form/IdentifiableObject/DataHandler/ProductFormDataHandler.php` — complex handler delegating to CommandBuilders (advanced use case)
- `src/PrestaShopBundle/Form/Admin/Configure/AdvancedParameters/Security/FormDataProvider.php` — settings page data provider

## Conventions

- **Default form base class:** standard entity forms extend `TranslatorAwareType` or `AbstractType`. `NavigationTabType` is only for complex multi-tab forms (exception, not the default)
- **Form types define structure only** — no knowledge of commands/queries. Validation via Symfony constraints on fields
- **IdentifiableObject pattern:** entity forms use `FormDataProviderInterface` (reads data for edit) + `FormDataHandlerInterface` (dispatches commands on create/update). These are encapsulated by two framework services:
  - `FormBuilder` (`PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Builder\FormBuilder`) — builds the Symfony form. For edit, calls `DataProvider::getData($id)` to pre-fill. For create, calls `DataProvider::getDefaultData()`. The controller calls `$this->getFormBuilder()->getForm(...)`
  - `FormHandler` (`PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Handler\FormHandler`) — handles form submission. Validates the form, then calls `DataHandler::create()` or `DataHandler::update()`. The controller calls `$this->getFormHandler()->handle($form)`
  - The controller never calls DataProvider or DataHandler directly — it goes through FormBuilder and FormHandler
- **DataProvider contract:** `getData($id): array` dispatches the Get query and maps DTO to form array structure. `getDefaultData(): array` returns defaults for create form — must match the same structure as `getData()`
- **DataHandler contract:** `create(array $data): mixed` builds Add command from form data and dispatches via command bus, returns new ID. `update($id, array $data): void` builds Edit command with setters for non-null fields. Sub-resource commands are dispatched separately after the main command
- **Multilingual fields:** use `TranslatableType` wrapping the inner field type (including `TextareaType` and `FormattedTextareaType` for multilingual textareas). Data is an array keyed by language ID
- **File uploads:** use `FileType` with `'mapped' => false, 'required' => false`. Actual file saving happens in the DataHandler, not the form type. The edit template renders the existing file via a custom Twig block
- **Money / decimal fields:** PrestaShop stores prices with 6 decimal places. Always set explicit decimal scale. Use `DecimalNumber` in commands — never native `float`
- **Choice providers:** dynamic select options use `ChoiceProviderInterface` services injected into form types. Keys are labels, values are DB IDs
- **Service registration:** form types tagged with `form.type`, DataProvider/DataHandler registered with `autowire: true` and `autoconfigure: true`. Service IDs follow `prestashop.core.form.identifiable_object.data_provider.{domain}_form_data_provider` (and `data_handler` equivalently)
- **Sub-resource dispatch order:** in `DataHandler::create()`/`update()`, dispatch the main entity command first, then sub-resource commands separately
- **Tab anchor IDs:** when using `NavigationTabType`, tab anchor IDs are derived from tab names and used by JS for error-driven tab navigation
- **Error handling:** server-side validation via Symfony constraints is the source of truth. JS tab error navigation is enhancement only

## Skills

| Skill | Trigger |
|-------|---------|
| [`create-form-type`](skills/create-form-type/SKILL.md) | "create form type for {Domain}" |
| [`create-form-tab-layout`](skills/create-form-tab-layout/SKILL.md) | "create tab layout for {Domain} form" |
| [`create-form-data-handling`](skills/create-form-data-handling/SKILL.md) | "create form data handling for {Domain}" |

