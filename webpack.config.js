let Encore = require('@symfony/webpack-encore');
const PathResolver = require('path');
const webpack = require('webpack');

webpackConfig = Encore.setOutputPath('./src/Bundle/IntegratedBundle/Resources/public')
    .setPublicPath('/bundles/integratedintegrated')
    .setManifestKeyPrefix('bundles/integratedintegrated')
    .addEntry('app', [
        './src/Bundle/ContentBundle/Resources/assets/sass/main.scss',
        './src/Bundle/WorkflowBundle/Resources/assets/css/style.css',
        './src/Bundle/ContentBundle/Resources/assets/js/main.js',
    ])
    .addEntry('iconoir', [
        './node_modules/iconoir/css/iconoir.css',
    ])
    .addEntry('pickr', [
        './src/Bundle/ContentBundle/Resources/assets/js/pickr.js',
    ])
    .addEntry('edit', [
        './node_modules/jquery-datetimepicker/jquery.datetimepicker.css',
        './src/Bundle/ContentBundle/Resources/assets/js/article_search.js',
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
        './src/Bundle/ContentBundle/Resources/assets/js/jqueryui.js',
        './src/Bundle/ContentBundle/Resources/assets/js/mediaGallery.js',
    ])
    .addEntry('mediagallery_selection', [
        './src/Bundle/ContentBundle/Resources/assets/js/mediagallery_selection.js',
    ])
    .addEntry('upload_uppy', [
        './src/Bundle/ContentBundle/Resources/assets/js/upload_uppy.js',
    ])
    .addEntry('edit_uppy', [
        './src/Bundle/ContentBundle/Resources/assets/js/edit_uppy.js',
    ])
    .addEntry('edit_panel', [
        './src/Bundle/ContentBundle/Resources/assets/js/edit_panel.js',
    ])
    .addEntry('iframe', [
        './src/Bundle/BlockBundle/Resources/assets/css/iframe.css',
    ])
    .addEntry('content_sortable', [
        './node_modules/components-jqueryui/jquery-ui.min.js',
        './src/Bundle/FormTypeBundle/Resources/assets/js/content_sortable_collection.js'
    ])
    .addEntry('drag-drop', [
        './node_modules/jquery.filer/css/jquery.filer.css',
        './node_modules/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css',
        './node_modules/jquery.filer/js/jquery.filer.js',
        './src/Bundle/StorageBundle/Resources/assets/css/drag-drop.css',
        './src/Bundle/StorageBundle/Resources/assets/js/drag-drop.js'
    ])
    .addEntry('workflow', [
        './src/Bundle/WorkflowBundle/Resources/assets/js/updateTransitions.js',
        './src/Bundle/WorkflowBundle/Resources/assets/js/defaultSelection.js',
        './src/Bundle/WorkflowBundle/Resources/assets/js/select2_init.js',
    ])
    .addEntry('article-search', [
        './src/Bundle/ContentBundle/Resources/assets/js/vue_init.js',
        './src/Bundle/ContentBundle/Resources/assets/js/article_search.js',
        './src/Bundle/ContentBundle/Resources/assets/sass/components/vue/vue.scss'
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
    .copyFiles({
        from: './src/Bundle/ContentBundle/Resources/assets/fonts',
        to: 'fonts/[path][name].[ext]',
    })
    .cleanupOutputBeforeBuild()
    .autoProvidejQuery()
    .enableVueLoader(() => {}, {
        runtimeCompilerBuild: false,
        version: 3
    })
    .addPlugin(
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: false,
            __VUE_PROD_DEVTOOLS__: false,
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
        })
    )
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            path: './postcss.config.js',
        };
    })
    .enableSassLoader(options => {
        options.implementation = require('sass');
    })
    .enableSourceMaps(false)
    .enableVersioning(false)
    .disableSingleRuntimeChunk()
    .addLoader({ test: /\.handlebars$/, loader: 'handlebars-loader' })
    .getWebpackConfig();

webpackConfig.resolve.alias = {
    typeahead: PathResolver.resolve(__dirname, 'node_modules/typeahead.js/dist/typeahead.bundle.js'),
    jquery: PathResolver.resolve(__dirname, 'node_modules/jquery/dist/jquery.js'),
    'vue': 'vue/dist/vue.esm-bundler.js',
};

webpackConfig.resolve.fallback = {'fs': false};

module.exports = webpackConfig;
