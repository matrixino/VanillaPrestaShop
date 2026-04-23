---
name: create-file-upload-field
description: >
  Documents how to implement file upload fields in PrestaShop Symfony forms. The
  uploaded file is handled in the DataHandler and saved to the `img/` directory
  via the PS file service.
needs: [create-form-type]
produces: "FileType configuration for image/logo upload fields"
conditional: "only if domain has file upload (A2 analysis found image/logo field)"
---

# create-file-upload-field

## Instructions

1. In form type: add `FileType::class` with `'mapped' => false, 'required' => false, 'constraints' => [new File(['mimeTypes' => ['image/*']])]`.
2. In DataHandler: `$file = $form->get('logo')->getData()`. If `$file !== null`, move to `_PS_IMG_DIR_` subdirectory.
3. Store the filename in the command's logo field setter.
4. For edit: display existing image in the form template (not handled by FileType — add custom Twig block).

## Rules

- File validation constraints go on the FileType field
- Actual file saving happens in the DataHandler, not the repository
