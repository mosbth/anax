<?php

$pages = [
    ''                  => ['title' => 'Home', 'file' => 'index.md'],
    'http-error-codes'  => ['title' => 'Exceptions as HTTP error codes'],
    'create-urls'       => ['title' => 'Creating urls'],
    'create-urls-in-md' => ['title' => 'Creating urls in text or Markdown'],
];

$app->router->add('*', function () use ($app, $pages) {

    $route = $app->request->getRoute();

    if (!isset($pages[$route])) {
        throw new \Anax\Exception\NotFoundException("The documentation page does not exists.");
    }

    $title = $pages[$route]['title'];
    $file  = isset($pages[$route]['file'])
        ? $pages[$route]['file']
        : $route . ".md";

    $app->theme->setTitle($title);

    $content = $app->fileContent->get($file);
    $content = $app->textFilter->doFilter($content, 'shortcode, markdown');

    $app->views->add('default/article', [
        'content' => $content,
    ]);

});

/*
// Default route
$app->router->add('*', function () use ($app) {
    ;
}
*/
