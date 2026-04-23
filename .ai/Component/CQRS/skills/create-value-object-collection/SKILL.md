---
name: create-value-object-collection
description: >
  Create a typed collection ValueObject when a domain field is a list of items
  rather than a scalar. Validates the collection at construction and provides
  iteration.
needs: [create-domain-value-objects]
produces: "Typed collection ValueObject for has-many relations (e.g., ShippingZoneCollection)"
conditional: "only for domains with sub-resource collections"
---

# create-value-object-collection

## Instructions

1. Create `final class {SubResource}Collection`.
2. Constructor takes `array $items` — validate each item type.
3. Implement `\Countable` and `\IteratorAggregate` for easy iteration.
4. Add `isEmpty(): bool` helper.

## Rules

- Collections are immutable once constructed
- Validate element types at construction — fail fast
