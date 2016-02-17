<?php
/**
 * Config-file for Anax, theme related settings, return it all as array.
 *
 */
return [

    /**
     * Base view to start render page from.
     */
    "view" => [
        "template" => "anax-base/index",

        "data" => [
            // General
            "htmlClass"     => [],
            "bodyClass"     => [],
            "lang"          => "sv",
            "charset"       => "utf-8",
            "title_append"  => " | Anax a web template",
            "favicon"       => "favicon.ico",

            // Style and stylesheets
            "stylesheets" => ["css/anax-base.min.css"],
            "styleInline" => null,

            // JavaScript
            "javascripts" => [],
        ],
    ],



    /**
     * Add default views to always include.
     */
    "views" => [
        [
            "region" => "header",
            "template" => "default/header",
            "data" => [
                "homeLink"      => "",
                "siteLogo"      => "img/anax.png",
                "siteLogoAlt"   => "Anax Logo",
                "siteTitle"     => "Anax PHP framework",
                "siteSlogan"    => "Reusable modules for web development"
            ],
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
            "data" => [
                "copyrightNotice" => "Copyright (c) 2013-2016 Mikael Roos (mos@dbwebb.se)",
                "linkToAnaxGitHub" => "https://github.com/mosbth/anax",
                "linkTextToAnaxGitHub" => "Anax på GitHub",
            ],
            "sort" => -1
        ],
        [
            "region" => "body-end",
            "template" => "default/google-analytics",
            "data" => [
                "account" => null,
            ],
            "sort" => -1
        ],
    ],
];
