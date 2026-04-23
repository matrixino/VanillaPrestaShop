---
name: create-doctrine-repository
description: >
  Create the Doctrine-based repository that extends
  `AbstractMultiShopObjectModelRepository`. This is the ONLY class that touches
  the database — all handlers delegate to it. Multistore support is never
  optional.
needs: [create-identity-value-object]
produces: "{Domain}Repository.php extending AbstractMultiShopObjectModelRepository — the single persistence entry point"
---

# create-doctrine-repository

## Instructions

1. Create `src/Adapter/{Domain}/{Domain}Repository.php`.
2. Extend `AbstractMultiShopObjectModelRepository` (namespace: `PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint`).
3. Inject `ShopConstraint` as a constructor parameter (or receive it per-method for multistore writes).
4. Implement `get{Domain}({Domain}Id $id): {LegacyObjectModel}` — loads the ObjectModel, throws `{Domain}NotFoundException` if not found.
5. Implement `create({Domain}): {Domain}Id` — calls ObjectModel `save()` or `add()`, wraps in try/catch, returns new ID.
6. Implement `update({Domain})` — calls ObjectModel `save()` or `update()`.
7. Implement `delete({Domain}Id $id, ShopConstraint $shopConstraint)` — calls `getShopIdsByConstraint()` for multistore-aware deletion.
8. For each sub-resource (A3 Section 6), implement `set{SubResource}s({Domain}Id, array $items)` using atomic replace.
9. The `getShopIdsByConstraint()` call is REQUIRED on every write in multistore mode — never skip it.

## Rules

- Every write method must call `getShopIdsByConstraint()` when multistore is active
- Never use `Db::getInstance()` — use Doctrine DBAL or ObjectModel methods
- Sub-resource writes always use delete-all + insert-all (atomic replace), never partial
- Throw typed domain exceptions, not generic exceptions
