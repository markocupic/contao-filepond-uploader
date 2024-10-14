const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/')
    .setPublicPath('/bundles/markocupiccontaofileponduploader')
    .setManifestKeyPrefix('')

    //.addEntry('backend', './assets/backend.js')
    .addEntry('frontend', './assets/filepond.js')

    .copyFiles({
        from: './assets',
        to: '[path][name].[hash:8].[ext]'
    })

    .copyFiles({
        from: './node_modules/filepond/dist',
        to: 'filepond/dist/[path][name].[hash:8].[ext]'
    })
    .copyFiles({
        from: './node_modules/filepond/locale',
        to: 'filepond/locale/[path][name].[hash:8].[ext]'
    })

    .copyFiles({
        from: './node_modules/filepond-plugin-file-validate-size/dist',
        to: 'filepond-plugin-file-validate-size/dist/[path][name].[hash:8].[ext]'
    })

    .copyFiles({
        from: './node_modules/filepond-plugin-file-validate-type/dist',
        to: 'filepond-plugin-file-validate-type/dist/[path][name].[hash:8].[ext]'
    })

    .copyFiles({
        from: './node_modules/filepond-plugin-image-edit/dist',
        to: 'filepond-plugin-image-edit/dist/[path][name].[hash:8].[ext]'
    })

    .copyFiles({
        from: './node_modules/filepond-plugin-image-exif-orientation/dist',
        to: 'filepond-plugin-image-exif-orientation/dist/[path][name].[hash:8].[ext]'
    })

    .copyFiles({
        from: './node_modules/filepond-plugin-image-preview/dist',
        to: 'filepond-plugin-image-preview/dist/[path][name].[hash:8].[ext]'
    })

    .copyFiles({
        from: './node_modules/filepond-plugin-image-validate-size/dist',
        to: 'filepond-plugin-image-validate-size/dist/[path][name].[hash:8].[ext]'
    })

    .copyFiles({
        from: './node_modules/filepond-plugin-image-resize/dist',
        to: 'filepond-plugin-image-resize/dist/[path][name].[hash:8].[ext]'
    })

    .copyFiles({
        from: './node_modules/filepond-plugin-image-transform/dist',
        to: 'filepond-plugin-image-transform/dist/[path][name].[hash:8].[ext]'
    })

    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps()
    .enableVersioning()

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    .enablePostCssLoader()
;

module.exports = Encore.getWebpackConfig();
