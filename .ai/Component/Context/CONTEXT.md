# Context Component

## Purpose

Immutable, injectable representations of the current execution environment: active shop, language, currency, country, and authenticated employee. Replaces the legacy `Context::getContext()` singleton. Does not perform authentication or session management.

## Layers

| Layer | Path |
|-------|------|
| Value objects | `src/Core/Context/` — `ShopContext`, `LanguageContext`, `CurrencyContext`, `CountryContext`, `EmployeeContext`, `LegacyControllerContext`, `ApiClientContext` |
| Builders | `src/Core/Context/*Builder.php` — one builder per context object, all implement `LegacyContextBuilderInterface` |
| Subscribers | `src/PrestaShopBundle/EventListener/Admin/Context/` — populate contexts from HTTP request on each Symfony event |

## Non-obvious patterns

- All context objects are `readonly` — built once per request by subscribers, never mutated
- Each builder has `buildLegacyContext()` to sync the old `Context::getContext()` global — necessary during the migration period
- `LanguageContext` implements both `LanguageInterface` **and** `LocaleInterface` — it can format numbers and prices directly

## Canonical examples

- `src/Core/Context/ShopContext.php`
- `src/Core/Context/ShopContextBuilder.php`

## Related

- [Configuration Component](../Configuration/CONTEXT.md) — `ShopContext::getShopConstraint()` scopes config writes
- [Locale Component](../Locale/CONTEXT.md) — `LanguageContext` wraps `LocaleInterface` for formatting
