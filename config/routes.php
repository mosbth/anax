<?php
/**
 * Add routes to the router, processed in the same order they are added.
 * The variabel $router holds the router.
 */



/**
 * Internal route for handling 403
 */
$router->addInternal("403", function () {
     $this->dispatcher->forward([
         "controller"   => "error",
         "action"       => "statusCode",
         "params"       => [
             "code"     => 403,
             "message"  => "HTTP Status Code 403: This is a forbidden route.",
         ],
     ]);
})->setName("403");



/**
 * Internal route for handling 404
 */
$router->addInternal("404", function () {
     $this->dispatcher->forward([
         "controller"   => "error",
         "action"       => "statusCode",
         "params"       => [
             "code"     => 404,
             "message"  => "HTTP Status Code 404: This route is not found.",
         ],
     ]);
     $this->dispatcher->forward([
         "controller"   => "error",
         "action"       => "displayValidRoutes",
     ]);
 })->setName("404");



/**
 * Internal route for handling 500
 */
$router->addInternal("500", function () {
     $this->dispatcher->forward([
         "controller"   => "error",
         "action"       => "statusCode",
         "params"       => [
             "code"     => 500,
             "message"  => "HTTP Status Code 500: There was an internal server or processing error.",
         ],
     ]);
 })->setName("500");



/**
 * Default route to load routes from /content
 */
$app->router->add("*", function () use ($app) {
    $content = $app->content->contentForRoute();
    foreach ($content->views as $view) {
        $app->views->add($view);
    }
    $app->theme->addFrontmatter($content->frontmatter);
});
