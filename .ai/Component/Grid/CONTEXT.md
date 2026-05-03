# Grid Component

## Purpose

Infrastructure for rendering and managing back-office data tables: column definitions, filters, row/bulk actions, query builders, data factories, and drag-and-drop position reordering. Does not contain any business data — each domain provides its own `GridDefinitionFactory` and Doctrine query builder.

## Layers

| Layer | Path |
|-------|------|
| Core contracts + factory | `src/Core/Grid/` |
| Column types, row/bulk actions | `src/Core/Grid/Column/`, `src/Core/Grid/Action/` |
| Query builder base | `src/Core/Grid/Query/AbstractDoctrineQueryBuilder.php` |
| Position updater | `src/Core/Grid/Position/` |
| Adapter utilities | `src/Adapter/Grid/` |

## Non-obvious patterns

- `AbstractGridDefinitionFactory` dispatches `action{GridId}GridDefinitionModifier` hook — modules add columns/actions without touching core code
- `SearchCriteriaInterface` is stored as a Symfony request attribute per grid, not a service — each grid type has its own `{Domain}Filters` class present in `src/Core/Search/Filters`
- For specific use cases a dedicated filter builder may be needed — see `src/Core/Search/Builder/TypedBuilder`
- Position updater (`GridPositionUpdater`) lives inside the Grid source tree but can be used by any entity that supports manual ordering, independent of grid rendering
- 60+ concrete query builders exist (one per domain grid) — all extend `AbstractDoctrineQueryBuilder` and implement `getSearchQueryBuilder()` + `getCountQueryBuilder()`

## Canonical examples

- `src/Core/Grid/Definition/Factory/AbstractGridDefinitionFactory.php` — base class showing the pattern every grid definition must follow
- `src/Core/Grid/Definition/Factory/ProductGridDefinitionFactory.php` — concrete implementation
- `src/Core/Grid/Query/LanguageQueryBuilder.php` — simple concrete query builder

## Factory trilogy (data flow)

Three factories work together to produce a renderable grid:

1. **`GridDefinitionFactory`** — defines structure: columns, filters, row actions, bulk actions. Each domain implements one extending `AbstractGridDefinitionFactory`. Hook `action{GridId}GridDefinitionModifier` allows modules to add columns/actions
2. **`GridDataFactory`** — retrieves data. `DoctrineGridDataFactory` delegates to a `DoctrineQueryBuilderInterface` implementation to execute SQL based on `SearchCriteria`. Returns `GridData` (RecordCollection + total count)
3. **`GridFactory`** — orchestrates: combines definition + data factory, resolves filters, dispatches hooks. This is what the controller calls: `$gridFactory->getGrid($filters)`

### GridDataFactory decoration

When grid data needs post-processing (e.g. resolving image URLs from IDs, formatting computed columns), decorate the `GridDataFactory` instead of modifying the query builder:

- Create a decorator implementing `GridDataFactoryInterface`
- Inject the original factory, call `getData()`, then transform the `RecordCollection`
- Register with `decorates:` in DI YAML

### SearchCriteria and {Domain}Filters

- `{Domain}Filters` (in `src/Core/Search/Filters/`) extends `Filters` which implements `SearchCriteriaInterface` — it is NOT a form type
- It defines defaults (grid ID, default sort column/direction, default limit)
- Injected into the controller's index action via argument resolver: `indexAction({Domain}Filters $filters)`
- Filter values come from the grid filter bar (saved in session by `CommonController::searchGridAction`)

## Conventions

### Column definitions

- Column IDs must be `snake_case` and **exactly match** the SQL column aliases in the query builder
- **Column ordering:** `BulkActionColumn` is always first, `ActionColumn` is always last. `PositionColumn` (if present) goes second after BulkActionColumn
- `ToggleColumn` requires a dedicated AJAX toggle route — it cannot work without one
- Grid definition must declare a `GRID_ID` const shared between the definition factory, the `{Domain}Filters` class, and the JS Grid constructor

### Query builders

- Always alias the primary key as `id_{domain}` (e.g. `id_tax`) — row actions use this alias for routing
- **Parameterized queries only** — never use raw string concatenation for filter values
- The count query must NOT include `LIMIT`/`OFFSET` — it returns the total before pagination
- When grid data needs post-processing (resolving image URLs, formatting computed columns), decorate the `GridDataFactory` instead of modifying the query builder

### Filters

- Filter fields are all **optional** — the grid works without any filter applied
- Filters are defined in the grid definition with specific form types (`TextType`, `ChoiceType`, `DateRangeType`, etc.)
- Filter values are saved in session by `CommonController::searchGridAction` and restored automatically

## Skills

| Skill | Trigger |
|-------|---------|
| [`create-grid-definition`](skills/create-grid-definition/SKILL.md) | "create grid definition for {Domain}" |
| [`create-grid-query-builder`](skills/create-grid-query-builder/SKILL.md) | "create grid query builder for {Domain}" |
| [`create-position-column`](skills/create-position-column/SKILL.md) | "add position column for {Domain}" |

## Related

- [PositionUpdater Component](../PositionUpdater/CONTEXT.md) — drag-and-drop reordering sub-layer (lives inside Grid source tree)
