const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .copyDirectory('node_modules/admin-lte/dist/img', 'public/img'); // Copia las imágenes de AdminLTE

// Añade estas líneas para compilar los CSS y JS de AdminLTE
mix.styles([
    'node_modules/admin-lte/plugins/fontawesome-free/css/all.min.css', // Font Awesome 5
    'node_modules/admin-lte/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css',
    'node_modules/admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css',
    'node_modules/admin-lte/plugins/jqvmap/jqvmap.min.css',
    'node_modules/admin-lte/dist/css/adminlte.min.css',
    'node_modules/admin-lte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css',
    'node_modules/admin-lte/plugins/daterangepicker/daterangepicker.css',
    'node_modules/admin-lte/plugins/summernote/summernote-bs4.css'
], 'public/css/adminlte.css');

mix.scripts([
    'node_modules/jquery/dist/jquery.min.js',
    'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', // Bootstrap 4 Bundle
    'node_modules/admin-lte/plugins/chart.js/Chart.min.js', // Chart.js
    'node_modules/admin-lte/plugins/sparklines/sparkline.js',
    'node_modules/admin-lte/plugins/jqvmap/jquery.vmap.min.js',
    'node_modules/admin-lte/plugins/jqvmap/maps/jquery.vmap.usa.js',
    'node_modules/admin-lte/plugins/jquery-knob/jquery.knob.min.js',
    'node_modules/admin-lte/plugins/moment/moment.min.js',
    'node_modules/admin-lte/plugins/daterangepicker/daterangepicker.js',
    'node_modules/admin-lte/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js',
    'node_modules/admin-lte/plugins/summernote/summernote-bs4.min.js',
    'node_modules/admin-lte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js',
    'node_modules/admin-lte/dist/js/adminlte.js',
    'node_modules/admin-lte/dist/js/pages/dashboard.js', // Dashboard specific JS
    'node_modules/admin-lte/dist/js/demo.js'
], 'public/js/adminlte.js');

// Opcional: para desarrollo, puedes usar live reload
// mix.browserSync('your-app-name.test'); // Reemplaza 'your-app-name.test' con la URL de tu entorno local



let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css');
