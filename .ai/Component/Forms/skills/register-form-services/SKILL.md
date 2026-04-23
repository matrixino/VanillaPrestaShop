---
name: register-form-services
description: >
  Register the form type, data provider, and data handler in the Symfony DI
  container. Ensures all form layer services are wired and discoverable by the
  controller and form factory.
needs: [create-form-type, create-form-data-provider, create-form-data-handler]
produces: "DI YAML registrations for form type, data provider, and data handler"
---

# register-form-services

## Instructions

1. Register `{Domain}Type` with `tag: form.type`.
2. Register `{Domain}FormDataProvider` with `autowire: true`.
3. Register `{Domain}FormDataHandler` with `autowire: true`.
4. Inject `CommandBus` and `QueryBus` into provider/handler if not autowired.
5. Run `php bin/console debug:container | grep {domain}_form` to verify.

## Rules

- All three form layer services (type, provider, handler) must be registered before wiring the controller
- Use autowire where possible — only add explicit arguments for bus injections
- Service IDs follow the pattern: `prestashop.core.form.identifiable_object.{domain}.data_provider`
