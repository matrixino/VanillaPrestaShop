---
name: create-controller-index-action
description: >
  Documents the full implementation of the grid index action. Builds the
  SearchCriteria from the request, presents the grid, and renders the listing
  template.
needs: [create-symfony-admin-controller, create-grid-definition, create-grid-query-builder, create-grid-filters]
produces: "indexAction() method — renders the grid listing page"
---

# create-controller-index-action

## Instructions

1. Inject `ResponseBuilder $responseBuilder` and `GridPresenter $gridPresenter` (or use `$this->get()`).
2. Build `SearchCriteria` from `Request $request` using the grid filters form.
3. Build grid: `$grid = $this->get('{domain}.grid_factory')->getGrid($searchCriteria)`.
4. Present grid: `$presentedGrid = $gridPresenter->present($grid)`.
5. Return `$this->render('@PrestaShop/Admin/{Section}/{Domain}/index.html.twig', ['grid' => $presentedGrid])`.
6. Handle the filter reset action: if `$request->request->has('grid[{domain}][action]')` is reset, redirect without filters.

## Rules

- Never build raw SQL in the controller — delegate to the grid factory
- SearchCriteria must be built from the request, not hardcoded
