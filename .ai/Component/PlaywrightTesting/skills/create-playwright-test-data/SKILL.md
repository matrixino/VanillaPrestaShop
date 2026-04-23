---
name: create-playwright-test-data
description: >
  Create the TypeScript test data module defining typed fixtures for minimal and
  full entity creation. These fixtures are imported by all Playwright test
  campaigns to ensure consistent test data.
produces: "tests/UI/data/{domain}.ts — typed test fixtures for all test scenarios"
---

# create-playwright-test-data

## Instructions

1. Create `tests/UI/data/{domain}.ts`.
2. Define a TypeScript interface for the entity: `interface {Domain}Data { name: string; active: boolean; ... }`.
3. Export `data{Domain}Minimal`: only required fields — the minimum to create a valid entity.
4. Export `data{Domain}Full`: all fields populated with test values.
5. For multilingual fields, include values for at least EN and FR.
6. For numeric fields, use realistic values (e.g., weight in kg, price > 0).
7. Keep fixture values deterministic — no `Math.random()`.

## Rules

- All fixture values must be deterministic — random values cause flaky tests
- Minimal fixture contains ONLY required fields — avoids masking missing required field validation
- Full fixture covers ALL fields so edit-tab campaigns can verify every field
