const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', '/assets/js/theme.js')
    .copyFiles({
        from: "./assets/img/",
        to: "[path][name].[hash:8].[ext]",
        context: "./assets"
    })
    .splitEntryChunks()

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')
    .enableSingleRuntimeChunk()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableStimulusBridge('./assets/json/controllers.json')
    .enableSassLoader(() => {
    }, {
        resolveUrlLoader: false
    })
    // .enableTypeScriptLoader()
    .enablePostCssLoader()
    .configureBabelPresetEnv((configuration) => {
        configuration.useBuiltIns = 'usage';
        configuration.corejs = '3.30';
    })
    .configureBabel((configuration) => {
        configuration.plugins.push('@babel/plugin-proposal-class-properties')
    })
    .configureTerserPlugin((configuration) => {
        configuration.extractComments = false;
    })
    .configureImageRule({
        filename: "img/[name].[hash:8][ext]",
    })
    .cleanupOutputBeforeBuild()
;

module.exports = Encore.getWebpackConfig();
