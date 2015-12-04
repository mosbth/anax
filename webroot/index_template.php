<?php

define('ANAX_INSTALL_PATH', realpath(__DIR__ . '/..'));
define('ANAX_APP_PATH', ANAX_INSTALL_PATH . '/app');

require ANAX_INSTALL_PATH . '/vendor/autoload.php';

$di  = new \Anax\DI\CDIFactoryDefault();
$app = new \Anax\Kernel\CAnax($di);

require ANAX_APP_PATH . '/config/routes.php';

$app->router->handle();
$app->theme->render();
