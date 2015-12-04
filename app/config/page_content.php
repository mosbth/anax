<?php
/**
 * Config-file for page content.
 *
 */
return [

    // Use for styling the menu
    'basepath' => ANAX_APP_PATH . '/content',

    // Default options for textfilter
    'textfilter' => 'shortcode, markdown',

    // Default view
    'view' => 'default/article',

    // Filter to load content
    'glob' => '*.md',


    // Define url to page details.
    'pages' => [
        ''          => [
            'title' => 'Home',
            'file'  => 'index.md',
        ],
        'testpage'  => [
            'title' => 'TestPage',
            'file'  => 'testpage.md',
        ],
    ],
];
