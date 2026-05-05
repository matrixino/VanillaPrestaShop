# Shipping Cost Calculation — Architecture

## Pattern: Calculator Pipeline

The architecture mirrors `src/Core/Pricing` (product pricing). It rests on three principles:

- **A mutable DTO `ShippingCostContext`** flows through the entire pipeline, enriched step by step.
- **Calculators** each have a single responsibility: read from and/or write to the context.
- **Providers** each encapsulate a single business concern for data retrieval.

---

## Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│  CALLER                                                                          │
│  (Handler, Service...)                                                           │
└────────────────────────────────┬────────────────────────────────────────────────┘
                                 │  ShippingCalculationRequest
                                 ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  ENTRY POINT  [Adapter]                                                          │
│                                                                                  │
│  ShippingCostCalculator                                                          │
│    1. creates ShippingCostContext from request                                   │
│    2. runs pipeline                                                              │
│    3. returns ShippingCostResult (taxExcluded / taxIncluded)                     │
└────────────────────────────────┬────────────────────────────────────────────────┘
                                 │  ShippingCostContext (mutable DTO)
                                 ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  PIPELINE  [Core orchestrator → Adapter calculators → Core calculators]          │
│                                                                                  │
│  ① ZoneResolutionCalculator  ─────────────────► AddressRepository (DBAL)        │
│     Resolves zoneId from addressId                state → country fallback       │
│                                                                                  │
│  ② CarrierDataCalculator  ─────────────────────► CarrierDataProviderInterface   │
│     Loads carrier config into context              └─ CarrierDataProvider        │
│     (method, rangeBehavior, handling, isFree)           └─ CarrierRepository    │
│     → sets isFreeShipping if carrier.is_free                                    │
│                                                                                  │
│  ③ WeightCalculator  [Core, no infra deps]                                      │
│     Computes total cart weight                                                   │
│     Σ (weight_attribute or weight) × quantity                                   │
│                                                                                  │
│  ④ FreeShippingCalculator  ────────────────────► FreeShippingCriteriaProviderInterface
│     Checks global free shipping thresholds         └─ ConfigFreeShippingCriteriaProvider
│     → sets isFreeShipping if total or weight           └─ PS_SHIPPING_FREE_PRICE
│       exceeds configured thresholds                    └─ PS_SHIPPING_FREE_WEIGHT
│                                                                                  │
│  ⑤ BaseRangeCostCalculator  ───────────────────► CarrierDataProviderInterface   │
│     Fetches base cost from carrier ranges           └─ CarrierDataProvider      │
│     (ps_delivery, by weight or by price)                └─ getDeliveryPriceBy*  │
│     → sets isFreeShipping if out-of-range (behavior=0)                          │
│                                                                                  │
│  ⑥ AdditionalProductCostCalculator  [Core, no infra deps]                      │
│     Adds per-product additional shipping costs                                   │
│     Σ additional_shipping_cost × quantity                                       │
│                                                                                  │
│  ⑦ HandlingCostCalculator                                                       │
│     Adds PS_SHIPPING_HANDLING global fee                                         │
│     only when carrier.shipping_handling = true                                   │
│                                                                                  │
│  ⑧ CurrencyConversionCalculator                                                 │
│     Converts accumulated cost from PS base currency                              │
│     to order currency via Tools                                                  │
│                                                                                  │
│  ⑨ TaxCalculator  ◄── ALWAYS LAST ────────────► ShippingTaxRateProviderInterface│
│     Applies carrier tax rate                       └─ ShippingTaxRateProvider   │
│     Writes costTaxExcluded + costTaxIncluded            └─ carrier.getTaxesRate()│
│     Writes zeros for free shipping case                                          │
└────────────────────────────────┬────────────────────────────────────────────────┘
                                 │  ShippingCostContext (fully populated)
                                 ▼
                         ShippingCostResult
                    { taxExcluded, taxIncluded }
```

---

## Class Reference

### Entry Point

#### `Adapter\Carrier\ShippingCostCalculator`
Public entry point for shipping cost calculation. Builds a `ShippingCostContext` from the input parameters (carrierId, addressId, products, cart total, currency), triggers the pipeline, and returns the final result (`taxExcluded` / `taxIncluded`). Thin wrapper — no business logic here.

---

### Pipeline DTO

#### `Core\Domain\Carrier\ShippingCost\ShippingCostContext`
Mutable DTO flowing through the entire pipeline. Each calculator reads and/or writes into it. Contains:
- **Input data**: carrierId, addressId, currencyId, orderTotal, physical products
- **Resolved data** (populated step by step): zoneId, totalWeight, carrierShippingData, rangeCost, isFreeShipping
- **Final outputs**: costTaxExcluded, costTaxIncluded

---

### Pipeline Interface

#### `Core\...\Calculator\ShippingCostCalculatorInterface`
Common interface for all calculators. Single contract: `compute(ShippingCostContext $context): void`.

---

### Core Calculators (no infrastructure dependencies)

#### `Core\...\Calculator\ShippingCostCalculator`
Pipeline orchestrator. Receives an `iterable<ShippingCostCalculatorInterface>` (Symfony tagged iterator, priority-sorted) and calls `compute()` on each step in order.

#### `Core\...\Calculator\WeightCalculator`
Computes total cart weight by summing `(weight_attribute or weight) × quantity` for each physical product. Skips if free shipping is already active.

#### `Core\...\Calculator\AdditionalProductCostCalculator`
Adds per-product additional shipping costs (`additional_shipping_cost × quantity`) to the running cost. Skips if free shipping is active.

---

### Adapter Calculators (with infrastructure dependencies)

#### `Adapter\...\Calculator\ZoneResolutionCalculator`
Resolves `zoneId` from `addressId` via `AddressRepository` (DBAL query: state → country fallback). Writes zoneId into the context for downstream calculators.

#### `Adapter\...\Calculator\CarrierDataCalculator`
Loads carrier configuration (method, range_behavior, handling, is_free) from `CarrierDataProviderInterface` and writes it into the context. Switches to free shipping if the carrier is not found or if `is_free = true`.

#### `Adapter\...\Calculator\FreeShippingCalculator`
Checks global free shipping thresholds (amount and/or weight) from `FreeShippingCriteriaProviderInterface`. Sets the `isFreeShipping` flag if either threshold is met.

#### `Adapter\...\Calculator\BaseRangeCostCalculator`
Fetches the base cost from carrier ranges (`ps_delivery`) via `CarrierDataProviderInterface::getRangeCost()`. If out of range with `range_behavior = 0`, switches to free shipping.

#### `Adapter\...\Calculator\HandlingCostCalculator`
Adds the global handling fee (`PS_SHIPPING_HANDLING`) to the running cost, only when the carrier has `shipping_handling = true`. Skips if free shipping is active.

#### `Adapter\...\Calculator\CurrencyConversionCalculator`
Converts the accumulated cost (stored in PS base currency) to the order currency via `Tools`. Skips if free shipping is active.

#### `Adapter\...\Calculator\TaxCalculator`
**Must always be the last calculator in the pipeline.** Applies the carrier tax rate (via `ShippingTaxRateProviderInterface`) to produce `costTaxExcluded` and `costTaxIncluded`. Writes zeros for the free shipping case. Sets precision from the currency context.

---

### Provider Interfaces (Core — contracts without infra dependencies)

#### `Core\...\Provider\ShippingCostProviderInterface`
Marker interface — all provider interfaces extend it. Makes the set of domain providers identifiable as a group.

#### `Core\...\Provider\CarrierDataProviderInterface`
Two related carrier responsibilities:
- `getCarrierShippingData(carrierId)` → `CarrierShippingData` (carrier config)
- `getRangeCost(carrierData, value, weight, zoneId, currencyId)` → `DecimalNumber|null` (price from delivery range)

#### `Core\...\Provider\FreeShippingCriteriaProviderInterface`
`getCriteria()` → `FreeShippingCriteria` (free shipping thresholds from PS configuration).

#### `Core\...\Provider\ShippingTaxRateProviderInterface`
`getTaxRate(carrierId, addressId)` → `float` (carrier tax rate for a given address).

---

### Provider DTOs (Core — infrastructure-free value objects)

#### `Core\...\Provider\CarrierShippingData`
Immutable value object representing carrier configuration: `shippingMethod`, `rangeBehavior`, `shippingHandling`, `isFreeMethod`.

#### `Core\...\Provider\FreeShippingCriteria`
Immutable value object holding thresholds: `freeShippingPrice` and `freeShippingWeight`. `hasFreePrice()` and `hasFreeWeight()` return true when the respective threshold is set and greater than zero.

---

### Provider Implementations (Adapter — data access)

#### `Adapter\...\Provider\CarrierDataProvider`
Implements `CarrierDataProviderInterface`. Uses `CarrierRepository` (legacy ObjectModel) to load the carrier and build `CarrierShippingData`. `getRangeCost()` delegates to `getDeliveryPriceByWeight()` or `getDeliveryPriceByPrice()` based on shipping method.

#### `Adapter\...\Provider\ConfigFreeShippingCriteriaProvider`
Implements `FreeShippingCriteriaProviderInterface`. Reads `PS_SHIPPING_FREE_PRICE` and `PS_SHIPPING_FREE_WEIGHT` from `Configuration` and returns a `FreeShippingCriteria`.

#### `Adapter\...\Provider\ShippingTaxRateProvider`
Implements `ShippingTaxRateProviderInterface`. Loads the carrier and address via their repositories, calls `getTaxesRate()` on the legacy carrier ObjectModel. Returns `0.0` on any exception.

---

## Typing Rules

- All amounts and weights use `DecimalNumber` (never `float`) throughout Core and Adapters.
- Legacy `Carrier` methods requiring floats (e.g. `getDeliveryPriceByWeight`): cast via `(float)(string)$decimalNumber`.
- `TaxCalculator` **must always be the last** calculator in the pipeline.
