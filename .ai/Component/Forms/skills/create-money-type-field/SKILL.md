---
name: create-money-type-field
description: >
  Documents the use of Symfony's MoneyType or PS-specific currency-aware type
  for price fields. Handles currency display and decimal precision.
needs: [create-form-type]
produces: "MoneyType field configuration for price/cost fields"
conditional: "only if domain has monetary fields (prices, shipping costs)"
---

# create-money-type-field

## Instructions

1. For static currency: use `MoneyType::class` with `'currency' => $defaultCurrencyIsoCode`.
2. For multi-currency: use PS-specific `AmountType` if available, or `NumberType` with currency symbol injected via template.
3. Ensure the field value is stored as a decimal with appropriate precision (typically 6 decimal places in PS).
4. Use `NumberTransformer` or `MoneyTransformer` to convert between form display and DB storage.

## Rules

- Always set explicit decimal scale — PS stores prices with 6 decimal places by default
- Do not use plain `NumberType` for prices without a transformer — floating-point rounding will corrupt values
