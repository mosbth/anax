<?php

$app->router->add("*", function () use ($app) {

    $app->content->useCache(false);
    $content = $app->content->contentForRoute();

    //var_dump($content);
    //var_dump($app->content->getIndex());

    $app->views->add($content->view, [
        "content" => $content->text,
        //"page" => $content->data,
    ]);
    
    $app->theme->addFrontmatter($content->frontmatter);

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
