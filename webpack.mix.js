let mix = require('laravel-mix');
mix.autoload({
    jquery: ['$', 'window.jQuery', 'jQuery']
});

mix.webpackConfig(
    {
        module: {
            rules: [
                {
                    test: /\.mjs$/, resolve: { fullySpecified: false },
                    include: /node_modules/,
                    type: "javascript/auto"
                }
            ]
        },
    }
);

mix.js('resources/js/admin.js', 'assets/js/adjuster_forge_admin.js').vue({ version: 3 })
    .js('resources/js/adjuster_forge_profile.js', 'assets/js/adjuster_forge_profile.js').vue({ version: 3 })
    .js('resources/js/adjuster_forge_auth.js', 'assets/js/adjuster_forge_auth.js').vue({ version: 3 })
    .sass('resources/css/adjuster_forge_admin.scss', 'assets/css/adjuster_forge_admin.css')
    .sass('resources/css/adjuster_forge_profile.scss', 'assets/css/adjuster_forge_profile.css')
    .sass('resources/css/adjuster_jobs.scss', 'assets/css/adjuster_jobs.css')
    .sass('resources/css/adjuster_forge_auth.scss', 'assets/css/adjuster_forge_auth.css')
    .copy('resources/img', 'assets/img')