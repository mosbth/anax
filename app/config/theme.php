<?php
/**
 * Config-file for Anax, theme related settings, return it all as array.
 *
 */
return [

    /**
     * Settings for Which theme to use, theme directory is found by path
     * and name.
     *
     * path:      path to the theme directory, end without slash.
     * template:  base template for the theme
     * functions: file to include holding theme specific functions, or null
     */
    "settings" => [
        "path"     => ANAX_INSTALL_PATH . "/theme/anax-base",
        "template" => "index.tpl.php",
        "function" => "function.php",
    ],

    
    /**
     * Add default views.
     */
    "views" => [
        [
            "region" => "header",
            "template" => "default/header",
            "data" => [],
            "sort" => -1
        ],
        [
            "region" => "navbar",
            "template" => "default/navbar",
            "data" => [],
            "sort" => -1
        ],
        [
            "region" => "footer",
            "template" => "default/footer",
            "data" => [],
            "sort" => -1
        ],
    ],


    /**
     * Data to extract and send as variables to the main template file.
     */
    "data" => [

        // Language for this page.
        "lang" => "sv",

        // Append this value to each <title>
        "title_append" => " | Anax a web template",

        // Stylesheets
        "stylesheets" => ["css/style.css"],

        // Inline style
        "style" => null,

        // Favicon
        "favicon" => "favicon.ico",

        // Path to modernizr or null to disable
        "modernizr" => "js/modernizr.js",

        // Path to jquery or null to disable
        "jquery" => "//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js",

        // Array with javscript-files to include
        "javascript_include" => [],

        // Use google analytics for tracking, set key or null to disable
        "google_analytics" => null,
    ],
];
