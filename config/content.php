<?php
/**
 * Config-file for page content.
 *
 */
return [

    // Use for styling the menu
    "basepath" => ANAX_APP_PATH . "/content",

    // Default options for textfilter
    "textfilter" => [
        "jsonfrontmatter",
        "yamlfrontmatter",
        "shortcode",
        "markdown",
        "titlefromh1"
    ],

    // Default view
    "view" => "default/article",

    // Filter to load content
    "pattern" => "*.md",

];
