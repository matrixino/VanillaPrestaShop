---
name: create-domain-interface
description: >
  Define the repository interface in the Core domain layer. The concrete
  implementation in Adapter implements this interface. Commands depend on the
  interface, not the concrete class — enabling adapter replacement.
needs: [create-doctrine-repository]
produces: "{Domain}RepositoryInterface.php — interface allowing adapter swapping"
---

# create-domain-interface

## Instructions

1. Create interface in `src/Core/Domain/{Domain}/`.
2. Declare all methods needed by command handlers: `get{Domain}`, `create`, `update`, `delete`, plus any sub-resource methods.
3. Use only Core domain types in the signature — no Doctrine/ObjectModel types.

## Rules

- Interface lives in Core, implementation in Adapter
- No Doctrine-specific types in the interface signatures
