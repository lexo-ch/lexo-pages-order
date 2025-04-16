const mix = require('laravel-mix');

mix
    .setPublicPath('dist');

mix
    .js('resources/scripts/admin-po.js', 'js')
    .sass('resources/styles/admin-po.scss', 'css')
    .options({
        processCssUrls: false,
    });

mix
    .version()
    .sourceMaps();
