# IntegratedFormTypeBundle #
The IntegratedFormTypeBundle provides different Symfony Form Types for the Integrated project.

## Operations Runbook

### Purpose
- Shared Symfony form types and form-related helpers used across Integrated bundles
- Media selection dialog endpoints used by content editors

### Commands
This bundle does not expose standalone console commands.

### Routes
Media dialog routes:
- `/media/tinymce/dialog/image`
- `/media/tinymce/dialog/gallery`
- `/media/tinymce/dialog/video`

### Cron And Workers
No dedicated cron worker.

### Verification
- Open TinyMCE media dialog endpoints in admin and verify rendering.
- Verify custom form types (for example sortable collection) are registered and usable.

### Troubleshooting
- Missing form type service: verify extension/service registration under `Resources/config/services.xml`.
- Media dialog route failures: verify route import from `routing/media.yaml`.

## Documentation ##
The documentation is stored in the `Resources/doc/index.md`.

[Read the Documentation](Resources/doc/index.md)

## Installation ##
The installation instructions can be found in the documentation.

## About ##
The IntegratedFormTypeBundle is part of the Intergrated project.

## Elements ##
 - integrated_sortable_collection
    - javascripts are loaded with integrated asset manager
    - the sortable items should have an order field with the following configuration

        $builder->add('order', 'hidden', [
            'attr' => [
                'data-itemorder' => 'collection',
            ],
        ]);

        or with annotations:

        * @Type\Field(type="hidden", options={"attr"={"data-itemorder"="collection"}})
