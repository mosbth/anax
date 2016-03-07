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

    "metafilter" => [
        "jsonfrontmatter",
        "yamlfrontmatter",
    ],

    // Default template
    "template" => "default/article",

    // Filter to load content
    "pattern" => "*.md",
    "meta" => ".meta.md",

];
