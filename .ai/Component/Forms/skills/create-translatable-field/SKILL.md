---
name: create-translatable-field
description: >
  Documents how to implement multilingual form fields using PrestaShop's
  `TranslatableType`. Multilingual fields render a separate input per language
  tab and submit a language-keyed array.
needs: [create-form-type]
produces: "TranslatableType field configuration for multilingual form fields"
conditional: "only if domain has multilingual fields (A2 lang=true fields)"
---

# create-translatable-field

## Instructions

1. Add multilingual field:
   ```php
   ->add('name', TranslatableType::class, [
       'type' => TextType::class,
       'options' => ['constraints' => [new NotBlank()]],
   ])
   ```
2. `TranslatableType` renders one input per active shop language.
3. Submitted data is `['name' => [1 => 'English name', 2 => 'French name']]`.
4. In F2/F3, map to command's `setLocalizedNames()` setter.
5. For textarea (description): use `TranslatableType` wrapping `TextareaType`.

## Rules

- TranslatableType field name must match the command's `setLocalized{Field}()` setter
- The form data structure (lang-keyed array) must match the DataProvider's output structure
