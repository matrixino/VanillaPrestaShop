---
name: create-identity-value-object
description: >
  Create the typed identity value object that wraps the integer primary key. All
  commands and queries that reference an existing entity take a `{Domain}Id`,
  never a raw int.
produces: "{Domain}Id.php — typed wrapper around the entity primary key"
---

# create-identity-value-object

## Instructions

1. Check migration-manifest.md for the primary key field name (usually `id_{domain}` in DB).
2. Create `src/Core/Domain/{Domain}/ValueObject/{Domain}Id.php`.
3. Declare `final class {Domain}Id` with a single `private int $value` property.
4. Constructor: validate `$value > 0`, throw `{Domain}Exception` with code `INVALID_ID` if not.
5. Add `getValue(): int` getter.
6. The class must be `final`, have `declare(strict_types=1)`, and carry no Symfony/Doctrine dependency.
7. Register nothing in DI — value objects are instantiated directly with `new`.

## Rules

- Never accept `0` or negative integers — throw a domain exception
- No Doctrine annotations or Symfony attributes on value objects
- This class lives in `Core/Domain/`, never in `Adapter/`
- The exception thrown must be the domain's own exception class, not `\InvalidArgumentException`
