let Encore = require('@symfony/webpack-encore');
const PathResolver = require('path');

webpackConfig = Encore.setOutputPath(
    './src/Bundle/IntegratedBundle/Resources/public')
    .setPublicPath('/bundles/integratedintegrated')
    .setManifestKeyPrefix('bundles/integratedintegrated')
    .addEntry('app', [
            './src/Bundle/ContentBundle/Resources/assets/sass/main.scss',
            './src/Bundle/WorkflowBundle/Resources/assets/css/style.css',
            './node_modules/@fortawesome/fontawesome-free/css/fontawesome.min.css',
            './node_modules/@fortawesome/fontawesome-free/css/solid.min.css',
            './node_modules/@fortawesome/fontawesome-free/css/regular.min.css',
            './src/Bundle/ContentBundle/Resources/assets/js/main.js'])
    .addEntry('edit', [
            './node_modules/jquery-datetimepicker/jquery.datetimepicker.css',
            './src/Bundle/ContentBundle/Resources/assets/js/edit.js',
            './src/Bundle/ContentBundle/Resources/assets/js/handlebars.helpers.js',
            './src/Bundle/UserBundle/Resources/assets/js/visible_user_form.js',
            './src/Bundle/WorkflowBundle/Resources/assets/js/workflowChangeState.js',
            './src/Bundle/FormTypeBundle/Resources/assets/css/tinymce.editor.css',
            './src/Bundle/FormTypeBundle/Resources/assets/js/editor.js',
    ])
    .addEntry('collection', [
        './src/Bundle/ContentBundle/Resources/assets/js/collection.js',
    ])
    .addEntry('mediagallery', [
        './src/Bundle/ContentBundle/Resources/assets/sass/main.scss',
        './src/Bundle/WorkflowBundle/Resources/assets/css/style.css',
        './src/Bundle/ContentBundle/Resources/assets/js/main.js',
        './src/Bundle/ContentBundle/Resources/assets/js/jqueryui.js',
        './src/Bundle/ContentBundle/Resources/assets/css/mediaGallery.css',
        './src/Bundle/ContentBundle/Resources/assets/js/mediaGallery.js',
    ])
    .addEntry('iframe', [
        './src/Bundle/BlockBundle/Resources/assets/css/iframe.css',
    ])
    .addEntry('content_sortable', [
        './node_modules/components-jqueryui/jquery-ui.min.js',
        './src/Bundle/FormTypeBundle/Resources/assets/js/content_sortable_collection.js'])
    .addEntry('drag-drop', [
        './node_modules/jquery.filer/css/jquery.filer.css',
        './node_modules/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css',
        './node_modules/jquery.filer/js/jquery.filer.js',
        './src/Bundle/StorageBundle/Resources/assets/css/drag-drop.css',
        './src/Bundle/StorageBundle/Resources/assets/js/drag-drop.js'])
    .addEntry('workflow', [
        './src/Bundle/WorkflowBundle/Resources/assets/js/updateTransitions.js',
        './src/Bundle/WorkflowBundle/Resources/assets/js/defaultSelection.js',
        './src/Bundle/WorkflowBundle/Resources/assets/js/select2_init.js',
    ])
    .copyFiles({
        from: './node_modules/tinymce/skins',
        to: 'skins/[path][name].[ext]'
    })
    .copyFiles({
        from: './node_modules/tinymce/icons',
        to: 'icons/[path][name].[ext]'
    })
    .copyFiles({
        from: './src/Bundle/ContentBundle/Resources/assets/images',
        to: 'images/[path][name].[ext]',
    })
    .cleanupOutputBeforeBuild()
    .autoProvidejQuery()
    .enableSassLoader()
    .enableSourceMaps(false)
    .enableVersioning(false)
    .disableSingleRuntimeChunk()
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            path: './postcss.config.js',
        };
    })
    .getWebpackConfig();


webpackConfig.resolve.alias = {
    typeahead: PathResolver.resolve(__dirname,
        'node_modules/typeahead.js/dist/typeahead.bundle.js'),
    jquery: PathResolver.resolve(__dirname,
        'node_modules/jquery/dist/jquery.js'),
};

webpackConfig.resolve.fallback = {'fs': false};

module.exports = webpackConfig;
