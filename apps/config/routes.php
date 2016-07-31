<?php

use Phalcon\Mvc\Router;

$di->set('router', function() {
    $router = new Router();

    $router->setDefaultModule('frontend');

    $router->add('/admin', [
        'module' => 'backend',
        'controller' => 'home',
        'action' => 'index',
    ]);

    $router->add('/admin/', [
        'module' => 'backend',
        'controller' => 'home',
        'action' => 'index',
    ]);

    $router->add('/admin/login', [
        'module' => 'backend',
        'controller' => 'login',
        'action' => 'index',
    ]);

    $router->add('/admin/products/:action', [
        'module' => 'backend',
        'controller' => 'products',
        'action' => 1,
    ]);

    $router->add('/products/:action', [
        'controller' => 'products',
        'action' => 1,
    ]);

    return $router;
});