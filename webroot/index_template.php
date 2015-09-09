<?php

define('ANAX_INSTALL_PATH', realpath(__DIR__ . '/..'));
define('ANAX_APP_PATH',     ANAX_INSTALL_PATH . '/app');

include ANAX_INSTALL_PATH . '/vendor/autoload.php';

$di  = new \Anax\DI\CDIFactoryDefault();
$app = new \Anax\Kernel\CAnax($di);

include ANAX_APP_PATH . '/config/routes.php';

$app->router->handle();
$app->theme->render();
