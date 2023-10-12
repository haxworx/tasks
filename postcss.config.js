if (process.env.NODE_ENV === 'production') {
    const purgecss = require('@fullhuman/postcss-purgecss')

    module.exports = {
        plugins: [
            purgecss({
                content: [
                    './templates/**/*.html.twig',
                    './vendor/symfony/twig-bridge/Resources/views/**/*.html.twig',
                    './node_modules/bootstrap/js/**/*.js',
                ],
            }),
        ]
    }
}
