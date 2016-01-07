<?php

$app->router->add("", function () use ($app) {

    $app->theme->setTitle("Home");

    $app->views->add("default/page", [
        "title"     => "The title",
        "content"   => "This is the content.",
    ]);

});



$app->router->add("about", function () use ($app) {

    $app->theme->setTitle("About");

    $content = $app->fileContent->get("about");

    $app->views->add("default/article", [
        "content" => $content
    ]);

});



$app->router->add("doc/*", function () use ($app) {

    list($title, $content, $toc) = $app->pageContent->getContentForRoute();

    $app->theme->setTitle($title);
    
    $app->views->add("default/article", ["content" => $content]);
    $app->views->add(
        "doc/toc",
        [
            "title" => t("The Manual"),
            "toc" => $toc,
        ],
        "sidebar-left"
    );

});



$app->router->add("guide/*", function () use ($app) {

    list($title, $content, $toc) = $app->pageContent->getContentForRoute();

    $app->theme->setTitle($title);
    
    $app->views->add("default/article", ["content" => $content]);
    $app->views->add(
        "doc/toc",
        [
            "title" => t("Guides"),
            "toc" => $toc,
        ],
        "sidebar-left"
    );

});



$app->router->add("*", function () use ($app) {

//    $app->pageContent->get();
/*
    $route = $app->request->getRoute();

    if (!isset($pages[$route])) {
        throw new \Anax\Exception\NotFoundException("The documentation page does not exists.");
    }

    $title = $pages[$route]["title"];
    $file  = isset($pages[$route]["file"])
        ? $pages[$route]["file"]
        : $route . ".md";

    $app->theme->setTitle($title);

    $content = $app->fileContent->get($file);
    $content = $app->textFilter->doFilter($content, "shortcode, markdown");

    $app->views->add("default/article", [
        "content" => $content,
    ]);
*/

});
