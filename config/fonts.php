<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Font Configuration
    |--------------------------------------------------------------------------
    |
    | This option controls the default font family used throughout the
    | application. Vazir is used as the primary font for Persian/Farsi
    | content with fallbacks for other languages.
    |
    */

    'default' => [
        'family' => 'Vazir, Tahoma, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
        'persian' => 'Vazir, Tahoma, Arial, sans-serif',
    ],

    /*
    |--------------------------------------------------------------------------
    | Font Weights
    |--------------------------------------------------------------------------
    |
    | Available font weights for Vazir font family
    |
    */

    'weights' => [
        'thin' => 100,
        'light' => 300,
        'regular' => 400,
        'medium' => 500,
        'bold' => 700,
        'black' => 900,
    ],

    /*
    |--------------------------------------------------------------------------
    | Font Paths
    |--------------------------------------------------------------------------
    |
    | Paths to font files for offline use
    |
    */

    'paths' => [
        'vazir' => '/fonts/vazir/fonts/webfonts/',
        'local' => public_path('fonts/vazir/fonts/webfonts/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | RTL Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for right-to-left languages
    |
    */

    'rtl' => [
        'enabled' => true,
        'languages' => ['fa', 'ar', 'he', 'ur'],
        'font_family' => 'Vazir, Tahoma, Arial, sans-serif',
    ],

];