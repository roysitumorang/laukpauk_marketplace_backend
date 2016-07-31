<?php

namespace Application\Backend;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\DiInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View\Engine\Volt;

class Module implements ModuleDefinitionInterface
{
    /**
     * Register a specific autoloader for the module.
     */
    function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces([
            'Application\Backend\Controllers' => APP_PATH . 'apps/backend/controllers/',
            'Application\Backend\Models'      => APP_PATH . 'apps/backend/models/',
            'Phalcon'                         => APP_PATH . 'apps/library/Phalcon/',
        ]);

        $loader->register();
    }

    /**
     * Register specific services for the module.
     */
    function registerServices(DiInterface $di)
    {
        /**
         * Start the session the first time some component request the session service
         */
        $di->setShared('session', function() use($di) {
            $session = new \Phalcon\Session\Adapter\Database([
                'db'    => $di->getDb(),
                'table' => 'sessions'
            ]);
            $session->start();
            return $session;
        });

        // Registering a dispatcher
        $di->set('dispatcher', function() {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace('Application\Backend\Controllers');

            return $dispatcher;
        });

        // Registering the view component
        $di->set('view', function() {
            $view = new View();
            $view->setViewsDir(APP_PATH . 'apps/backend/views/');
            $view->registerEngines([
                '.volt' => function($view, $di) {
                    $volt = new Volt($view, $di);
                    $volt->setOptions([
                        'compiledPath'      => APP_PATH . 'apps/backend/cache/',
                        'compiledSeparator' => '_',
                    ]);
                    $volt->getCompiler()->addFunction('is_a', 'is_a')
                        ->addFunction('count', 'count')
                        ->addFunction('number_format', 'number_format')
                        ->addFunction('date', 'date')
                        ->addFunction('strtotime', 'strtotime');

                    return $volt;
                },
            ]);

            return $view;
        });
    }
}
