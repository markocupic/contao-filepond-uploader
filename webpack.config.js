const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/')
    .setPublicPath('/bundles/markocupiccontaofileponduploader')
    .setManifestKeyPrefix('')

    .addEntry('filepond', './assets/filepond.js')

    .copyFiles({
        from: './node_modules/filepond/locale', to: 'filepond/locale/[path][name].[hash:8].[ext]'
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

    .enablePostCssLoader();

module.exports = Encore.getWebpackConfig();
