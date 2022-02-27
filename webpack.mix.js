let mix = require('laravel-mix');

mix.js('resources/js/dashboard.jsx', 'public/js').react()
    .postCss("resources/css/dashboard.pcss", 'public/css', [
        require('tailwindcss/nesting'),
        require('tailwindcss')
    ])
    .postCss('resources/css/admin.pcss', 'public/css')
    .options({
        processCssUrls: false
    })
    .webpackConfig({
        externals: {
            "react": "React",
            "jquery": "jQuery"
        }
    });
