---
name: create-domain-exception
description: >
  Create the domain exception hierarchy — a base exception plus specific typed
  exceptions for not-found and constraint violations. Controllers and callers
  catch these typed exceptions to display appropriate user messages.
produces: "{Domain}Exception.php + {Domain}NotFoundException.php + Cannot{Action}{Domain}Exception.php — typed exception hierarchy"
---

# create-domain-exception

## Instructions

1. Create base `{Domain}Exception extends \RuntimeException` with integer constants for each error code.
2. Create `{Domain}NotFoundException extends {Domain}Exception` — thrown when entity not found by ID.
3. Create `Cannot{Action}{Domain}Exception extends {Domain}Exception` for each constraint violation (e.g., `CannotDeleteCarrierException` if carrier is in use by orders).
4. Error code constants (e.g., `CARRIER_NOT_FOUND = 1`) allow callers to switch on error type.
5. Check A1 audit for business rules that prevent deletion/editing — create matching exception classes.

## Rules

- Never throw generic `\Exception` or `\RuntimeException` from domain code — always a typed domain exception
- Exception classes contain no business logic — they are typed signals only
- Error code constants must be unique integers within the class
