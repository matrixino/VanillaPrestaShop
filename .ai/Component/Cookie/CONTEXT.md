# Cookie Component

## Purpose

Encrypted HTTP cookie handling for front- and back-office: writing/reading encrypted key-value payloads, session token storage, and SameSite/Secure policy constants. Does not manage Symfony sessions — it is the lower-level transport they are stored in.

## Layers

| Layer | Path |
|-------|------|
| Legacy core | `classes/Cookie.php` — magic `__get`/`__set` over an encrypted payload |
| Modern constants | `src/Core/Http/CookieOptions.php` — `SAMESITE_*` constants and `MAX_COOKIE_VALUE` |

## Non-obvious patterns

- The **entire cookie payload is encrypted** as a single string with `PhpEncryption` — there is no plain-text cookie key
- `session_id` and `session_token` are stored **inside** the encrypted cookie payload, not as separate cookies
- No modern DI wrapper exists — `Cookie` is still instantiated directly in legacy bootstrap

## Canonical examples

- `classes/Cookie.php`

## Related

- [Context Component](../Context/CONTEXT.md) — `EmployeeContext` is built after cookie-based session validation
