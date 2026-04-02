# PrestaShop — AI Context (Root)

> For folder structure and navigation, see [STRUCTURE.md](STRUCTURE.md).
> For cross-domain naming traps and identity gotchas, see [GOTCHAS.md](GOTCHAS.md).
> For multi-store scoping (ShopConstraint, AbstractMultistoreConfiguration), see [MULTISTORE.md](MULTISTORE.md).

## Project overview

PrestaShop is an open-source e-commerce platform built on Symfony. It follows a progressive migration from a legacy architecture (ObjectModel, legacy controllers) toward a modern Domain-Driven Design approach (CQRS, Symfony controllers, Doctrine).

## Architecture layers

| Layer | Location | Role |
|-------|----------|------|
| Core Domain | `src/Core/Domain/` | Business logic: Commands, Queries, Handlers, ValueObjects, Exceptions |
| Core Components | `src/Core/` (non-Domain) | Shared infrastructure: Grid, Form, Hook, Translation, etc. |
| Adapter | `src/Adapter/` | Bridges between Core and legacy code or external systems |
| PrestaShopBundle | `src/PrestaShopBundle/` | Symfony bundle: controllers, form types, Twig extensions, DI config |
| Legacy | `classes/`, `controllers/` | Legacy ObjectModel classes and controllers — do not extend, migrate instead |
| Admin front-end | `admin-dev/themes/new-theme/` | Back-office UI: Vue.js components, JavaScript, SCSS |
| Front-office themes | `themes/` | Customer-facing Smarty templates |
| Modules | `modules/` | Native and third-party modules |
| Tests | `tests/` | PHPUnit (unit/integration), Behat (behavior), Playwright (UI) |

## Coding standards

- Every PHP file: `declare(strict_types=1);`
- Classes `final` by default; all parameters, return types, and properties must be typed
- No ObjectModel in new code — use Doctrine entities or CQRS commands
- All services defined in YAML; no `new` in controllers
- No `Db::getInstance()` in new code — use Doctrine repositories
- No business logic in controllers — delegate to Handlers
- Catch specific domain exceptions, not generic `\Exception`

## CQRS pattern

- **Commands** — write intentions dispatched via `CommandBus`
- **Queries** — read intentions dispatched via `QueryBus`
- **Handlers** — implement logic; never call other handlers (compose at controller level)
- Handler interfaces in `src/Core/Domain/{Domain}/CommandHandler|QueryHandler/`
- Concrete implementations in `src/Adapter/{Domain}/CommandHandler|QueryHandler/`

## Testing

| Type | Framework | Location |
|------|-----------|----------|
| Unit | PHPUnit | `tests/Unit/` |
| Integration | PHPUnit | `tests/Integration/` |
| Behavior | Behat | `tests/Integration/Behaviour/` |
| UI | Playwright | `tests/UI/` |

## Skills

| Skill | Path | Trigger |
|-------|------|---------|
| `create-skill` | [skills/create-skill/SKILL.md](skills/create-skill/SKILL.md) | "create a skill for …" |
| `domain-context-generator` | [skills/domain-context-generator/SKILL.md](skills/domain-context-generator/SKILL.md) | "generate context for [Domain]" |
| `component-context-generator` | [skills/component-context-generator/SKILL.md](skills/component-context-generator/SKILL.md) | "generate context for [Component]" |
| `create-form` | [skills/create-form/SKILL.md](skills/create-form/SKILL.md) | "create a form for [Entity]" |

## Domain contexts

All 57 domains under `src/Core/Domain/` have a context file. Read the relevant one before working in a domain.

| Domain | Context |
|--------|---------|
| Address | [Domain/Address/CONTEXT.md](Domain/Address/CONTEXT.md) |
| Alias | [Domain/Alias/CONTEXT.md](Domain/Alias/CONTEXT.md) |
| ApiClient | [Domain/ApiClient/CONTEXT.md](Domain/ApiClient/CONTEXT.md) |
| Attachment | [Domain/Attachment/CONTEXT.md](Domain/Attachment/CONTEXT.md) |
| AttributeGroup | [Domain/AttributeGroup/CONTEXT.md](Domain/AttributeGroup/CONTEXT.md) |
| Carrier | [Domain/Carrier/CONTEXT.md](Domain/Carrier/CONTEXT.md) |
| Cart | [Domain/Cart/CONTEXT.md](Domain/Cart/CONTEXT.md) |
| CartRule | [Domain/CartRule/CONTEXT.md](Domain/CartRule/CONTEXT.md) |
| CatalogPriceRule | [Domain/CatalogPriceRule/CONTEXT.md](Domain/CatalogPriceRule/CONTEXT.md) |
| Category | [Domain/Category/CONTEXT.md](Domain/Category/CONTEXT.md) |
| CmsPage | [Domain/CmsPage/CONTEXT.md](Domain/CmsPage/CONTEXT.md) |
| CmsPageCategory | [Domain/CmsPageCategory/CONTEXT.md](Domain/CmsPageCategory/CONTEXT.md) |
| Combination | [Domain/Combination/CONTEXT.md](Domain/Combination/CONTEXT.md) — code lives under `src/Core/Domain/Product/Combination/`, not a standalone domain |
| Configuration | [Domain/Configuration/CONTEXT.md](Domain/Configuration/CONTEXT.md) |
| Contact | [Domain/Contact/CONTEXT.md](Domain/Contact/CONTEXT.md) |
| Country | [Domain/Country/CONTEXT.md](Domain/Country/CONTEXT.md) |
| CreditSlip | [Domain/CreditSlip/CONTEXT.md](Domain/CreditSlip/CONTEXT.md) |
| Currency | [Domain/Currency/CONTEXT.md](Domain/Currency/CONTEXT.md) |
| Customer | [Domain/Customer/CONTEXT.md](Domain/Customer/CONTEXT.md) |
| CustomerMessage | [Domain/CustomerMessage/CONTEXT.md](Domain/CustomerMessage/CONTEXT.md) |
| CustomerService | [Domain/CustomerService/CONTEXT.md](Domain/CustomerService/CONTEXT.md) |
| Discount | [Domain/Discount/CONTEXT.md](Domain/Discount/CONTEXT.md) |
| Employee | [Domain/Employee/CONTEXT.md](Domain/Employee/CONTEXT.md) |
| Feature | [Domain/Feature/CONTEXT.md](Domain/Feature/CONTEXT.md) |
| Hook | [Domain/Hook/CONTEXT.md](Domain/Hook/CONTEXT.md) |
| ImageSettings | [Domain/ImageSettings/CONTEXT.md](Domain/ImageSettings/CONTEXT.md) |
| Language | [Domain/Language/CONTEXT.md](Domain/Language/CONTEXT.md) |
| MailTemplate | [Domain/MailTemplate/CONTEXT.md](Domain/MailTemplate/CONTEXT.md) |
| Manufacturer | [Domain/Manufacturer/CONTEXT.md](Domain/Manufacturer/CONTEXT.md) |
| Meta | [Domain/Meta/CONTEXT.md](Domain/Meta/CONTEXT.md) |
| Module | [Domain/Module/CONTEXT.md](Domain/Module/CONTEXT.md) |
| Notification | [Domain/Notification/CONTEXT.md](Domain/Notification/CONTEXT.md) |
| Order | [Domain/Order/CONTEXT.md](Domain/Order/CONTEXT.md) |
| OrderMessage | [Domain/OrderMessage/CONTEXT.md](Domain/OrderMessage/CONTEXT.md) |
| OrderReturn | [Domain/OrderReturn/CONTEXT.md](Domain/OrderReturn/CONTEXT.md) |
| OrderReturnState | [Domain/OrderReturnState/CONTEXT.md](Domain/OrderReturnState/CONTEXT.md) |
| OrderState | [Domain/OrderState/CONTEXT.md](Domain/OrderState/CONTEXT.md) |
| Position | [Domain/Position/CONTEXT.md](Domain/Position/CONTEXT.md) |
| Product | [Domain/Product/CONTEXT.md](Domain/Product/CONTEXT.md) |
| Profile | [Domain/Profile/CONTEXT.md](Domain/Profile/CONTEXT.md) |
| Search | [Domain/Search/CONTEXT.md](Domain/Search/CONTEXT.md) |
| SearchEngine | [Domain/SearchEngine/CONTEXT.md](Domain/SearchEngine/CONTEXT.md) |
| Security | [Domain/Security/CONTEXT.md](Domain/Security/CONTEXT.md) |
| Shipment | [Domain/Shipment/CONTEXT.md](Domain/Shipment/CONTEXT.md) |
| Shop | [Domain/Shop/CONTEXT.md](Domain/Shop/CONTEXT.md) |
| ShowcaseCard | [Domain/ShowcaseCard/CONTEXT.md](Domain/ShowcaseCard/CONTEXT.md) |
| SqlManagement | [Domain/SqlManagement/CONTEXT.md](Domain/SqlManagement/CONTEXT.md) |
| State | [Domain/State/CONTEXT.md](Domain/State/CONTEXT.md) |
| Store | [Domain/Store/CONTEXT.md](Domain/Store/CONTEXT.md) |
| Supplier | [Domain/Supplier/CONTEXT.md](Domain/Supplier/CONTEXT.md) |
| Tab | [Domain/Tab/CONTEXT.md](Domain/Tab/CONTEXT.md) |
| Tag | [Domain/Tag/CONTEXT.md](Domain/Tag/CONTEXT.md) |
| Tax | [Domain/Tax/CONTEXT.md](Domain/Tax/CONTEXT.md) |
| TaxRulesGroup | [Domain/TaxRulesGroup/CONTEXT.md](Domain/TaxRulesGroup/CONTEXT.md) |
| Theme | [Domain/Theme/CONTEXT.md](Domain/Theme/CONTEXT.md) |
| Title | [Domain/Title/CONTEXT.md](Domain/Title/CONTEXT.md) |
| Webservice | [Domain/Webservice/CONTEXT.md](Domain/Webservice/CONTEXT.md) |
| Zone | [Domain/Zone/CONTEXT.md](Domain/Zone/CONTEXT.md) |

## Component contexts

All 22 shared infrastructure components have a context file.

| Component | Context |
|-----------|---------|
| BackOfficeHelp | [Component/BackOfficeHelp/CONTEXT.md](Component/BackOfficeHelp/CONTEXT.md) |
| Configuration | [Component/Configuration/CONTEXT.md](Component/Configuration/CONTEXT.md) |
| Console | [Component/Console/CONTEXT.md](Component/Console/CONTEXT.md) |
| Context | [Component/Context/CONTEXT.md](Component/Context/CONTEXT.md) |
| Cookie | [Component/Cookie/CONTEXT.md](Component/Cookie/CONTEXT.md) |
| CQRS | [Component/CQRS/CONTEXT.md](Component/CQRS/CONTEXT.md) |
| Database | [Component/Database/CONTEXT.md](Component/Database/CONTEXT.md) |
| Export | [Component/Export/CONTEXT.md](Component/Export/CONTEXT.md) |
| FacetedSearch | [Component/FacetedSearch/CONTEXT.md](Component/FacetedSearch/CONTEXT.md) |
| Forms | [Component/Forms/CONTEXT.md](Component/Forms/CONTEXT.md) |
| GlobalJS | [Component/GlobalJS/CONTEXT.md](Component/GlobalJS/CONTEXT.md) |
| Grid | [Component/Grid/CONTEXT.md](Component/Grid/CONTEXT.md) |
| Hook | [Component/Hook/CONTEXT.md](Component/Hook/CONTEXT.md) |
| Import | [Component/Import/CONTEXT.md](Component/Import/CONTEXT.md) |
| Link | [Component/Link/CONTEXT.md](Component/Link/CONTEXT.md) |
| Locale | [Component/Locale/CONTEXT.md](Component/Locale/CONTEXT.md) |
| MailTemplate | [Component/MailTemplate/CONTEXT.md](Component/MailTemplate/CONTEXT.md) |
| PositionUpdater | [Component/PositionUpdater/CONTEXT.md](Component/PositionUpdater/CONTEXT.md) |
| Router | [Component/Router/CONTEXT.md](Component/Router/CONTEXT.md) |
| Smarty | [Component/Smarty/CONTEXT.md](Component/Smarty/CONTEXT.md) |
| TinyMCE | [Component/TinyMCE/CONTEXT.md](Component/TinyMCE/CONTEXT.md) |
| Twig | [Component/Twig/CONTEXT.md](Component/Twig/CONTEXT.md) |

## Generated indexes

Regenerate with `bash bin/generate-ai-index.sh`. **Read these before grepping** — they are pre-built snapshots faster than filesystem searches. **Regenerate after:** adding Commands/Queries, adding routes, adding hooks, or merging domain changes.

| File | Contents | When to use |
|------|----------|-------------|
| [generated/cqrs.md](generated/cqrs.md) | All Commands + Queries grouped by domain | Before adding a Command/Query — check it doesn't already exist |
| [generated/routes.md](generated/routes.md) | Symfony admin/API routes | Before adding a route or looking up a controller action |
| [generated/entities.md](generated/entities.md) | Doctrine entity columns and relations | Before writing a query or migration |
| [generated/hooks.md](generated/hooks.md) | Hook names discovered in source | Before dispatching or listening to a hook |
