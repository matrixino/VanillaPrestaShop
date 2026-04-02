# Forms Component

## Purpose

Infrastructure for building, populating, and handling back-office forms tied to identifiable entities: data providers, data handlers, command builders, and choice providers. Does not contain Symfony form type definitions — those live in `src/PrestaShopBundle/Form/Admin/`.

## Layers

| Layer | Path |
|-------|------|
| Core contracts (legacy simple forms) | `src/Core/Form/FormHandlerInterface.php`, `FormDataProviderInterface.php` |
| IdentifiableObject sub-layer (modern) | `src/Core/Form/IdentifiableObject/` |
| CommandBuilder sub-layer | `src/Core/Form/IdentifiableObject/CommandBuilder/` |
| Choice providers (Core, 61+) | `src/Core/Form/ChoiceProvider/` |
| Choice providers (Adapter, 26) | `src/Adapter/Form/ChoiceProvider/` |
| Symfony form types + utilities | `src/PrestaShopBundle/Form/Admin/` |

## Non-obvious patterns

- Two distinct patterns coexist: legacy `FormHandlerInterface` (settings pages, no `CommandBus`) and modern `IdentifiableObject` layer (entity forms, dispatches CQRS commands)
- `CommandBuilder` bridges raw form `array` → typed CQRS commands; Product domain has 16 builders, Combination has 6 — one per form section, not one per entity
- `FormDataHandlerInterface` has two methods: `create(array $data)` and `update($id, array $data)` — both return the entity ID
- `FormOptionsProviderInterface` supplies **dynamic** form options (carriers, tax rules) evaluated at render time, distinct from static choice providers

## Canonical examples

- `src/Core/Form/IdentifiableObject/Builder/FormBuilder.php` + `src/Core/Form/IdentifiableObject/Handler/FormHandler.php`
- `src/Core/Form/IdentifiableObject/CommandBuilder/Product/ProductCommandsBuilder.php`
- `src/Core/Form/IdentifiableObject/DataHandler/ProductFormDataHandler.php`

## Skills

- [`create-form`](skills/create-form/SKILL.md) — creates a complete IdentifiableObject form (form type, DataProvider, DataHandler, DI config, controller wiring)

## Related

- [CQRS Component](../CQRS/CONTEXT.md) — `FormDataHandler` implementations dispatch commands via `CommandBus`
- [Grid Component](../Grid/CONTEXT.md) — filter forms for grids use `FormChoiceProviderInterface`
- [Product Domain](../../Domain/Product/CONTEXT.md) — heaviest consumer; 16 CommandBuilders + dedicated DataHandler/DataProvider/OptionsProvider
