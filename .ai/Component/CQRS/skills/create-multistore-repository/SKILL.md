---
name: create-multistore-repository
description: >
  Documents the required multistore pattern in every PrestaShop repository. The
  `getShopIdsByConstraint()` method from
  `AbstractMultiShopObjectModelRepository` must be called on every write to
  resolve which shop contexts to apply the change to.
needs: [create-doctrine-repository]
produces: "Pattern for implementing multistore-aware writes using getShopIdsByConstraint()"
---

# create-multistore-repository

## Instructions

1. Inject or receive `ShopConstraint` in write methods.
2. Call `$shopIds = $this->getShopIdsByConstraint($shopConstraint)` at the start of every write method.
3. Iterate `$shopIds` and apply the write for each shop context.
4. For all-shops mode: `ShopConstraint::allShops()`.
5. For single-shop: `ShopConstraint::shop($shopId)`.
6. For shop-group: `ShopConstraint::shopGroup($groupId)`.
7. Never hard-code `Context::getContext()->shop->id` in repositories.

## Rules

- getShopIdsByConstraint() is never optional — call it on every write
- Do not read Context directly in repositories — receive ShopConstraint as parameter
- Single-shop installs still go through this path (returns an array with one ID)
