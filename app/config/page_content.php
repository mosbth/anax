<?php
/**
 * Config-file for text filters.
 *
 */
return [

    // Use for styling the menu
    'basepath' => ANAX_APP_PATH . '/content',

    // Define url to page details.
    'pages' => [
        ''          => ['title' => 'Home', 'file' => 'index.md'],
        'testpage'  => ['title' => 'TestPage', 'file' => 'testpage.md'],
    ],
];
