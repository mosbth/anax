<?php

$app->router->add("*", function () use ($app) {

    $app->content->useCache(false);
    $content = $app->content->contentForRoute();

    var_dump($content);
    //var_dump($app->content->getIndex());

    $app->views->add($content->template, $content->data);

    foreach ($content->views as $view) {
        $app->views->add($view);
    }

    $app->theme->addFrontmatter($content->frontmatter);


});
