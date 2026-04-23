---
name: register-cqrs-services
description: >
  Register all domain handlers and the repository in the Symfony DI container.
  Commands and queries route to handlers via command/query bus tags.
needs: [create-command-handler-interface, create-query-handler-interface, implement-create-command-handler, implement-edit-command-handler, implement-delete-command-handler, implement-get-query-handler, implement-list-query-handler, implement-bulk-delete-handler, implement-bulk-status-handler, implement-toggle-status-handler, implement-sub-resource-handler, create-doctrine-repository]
produces: "DI YAML service registrations for all handlers and repository"
---

# register-cqrs-services

## Instructions

1. Find the correct YAML file (check where other domain services are registered — may be per-domain or grouped).
2. Register `{Domain}Repository` with `autowire: true`.
3. For each handler, register the concrete class and tag it with `prestashop.command_handler` or `prestashop.query_handler`.
4. Tag format: `{ name: 'tactician.handler', command: 'PrestaShop\Core\Domain\{Domain}\Command\Add{Domain}Command' }`.
5. Register handler interfaces and bind concrete implementations.
6. Run `php bin/console debug:container | grep {domain}` to verify registration.

## Rules

- All services must be explicitly registered or use `autowire: true` — no magic
- Each handler tagged exactly once — duplicate tags cause multiple handler errors
- Check the tactician bus configuration for the correct tag format
