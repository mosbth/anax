<?php

define('ANAX_INSTALL_PATH', realpath(__DIR__ . '/..'));
define('ANAX_APP_PATH', ANAX_INSTALL_PATH . '/app');

require ANAX_INSTALL_PATH . '/vendor/autoload.php';

$di  = new \Anax\DI\CDIFactoryDefault();
$app = new \Anax\Kernel\CAnax($di);

$routes = ANAX_INSTALL_PATH . "/config/routes.php";
if (is_file(ANAX_APP_PATH . "/config/routes.php")) {
    $routes = ANAX_APP_PATH . "/config/routes.php";
}
require $routes;

$app->router->handle();
$app->theme->render();
