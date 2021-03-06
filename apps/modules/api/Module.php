<?php

namespace Modules\Modules\Api;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\DiInterface;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

class Module implements ModuleDefinitionInterface
{
    /**
     * Registers the module auto-loader
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces(
            [
                'Modules\Modules\Api\Controllers' => __DIR__ . '/controllers/',
                'Modules\Models\Entities' => __DIR__ . '/../../models/entities/',
                'Modules\Models\Services' => __DIR__ . '/../../models/services/',
                'Modules\Models\Repositories' => __DIR__ . '/../../models/repositories/'
            ]
        );

        $loader->register();
    }

    /**
     * Registers the module-only services
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        /**
         * Read configuration
         */
        $config = include CONF_PATH . "/config.php";

        /**
         * Setting up the view component
         */
        $di['view'] = function () {
            $view = new View();
            // Disable several levels
            $view->disableLevel(
                [
                    View::LEVEL_LAYOUT => true,
                    View::LEVEL_MAIN_LAYOUT => true,
                ]
            );
            return $view;
        };

        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
        $di['db'] = function () use ($config) {
            return new DbAdapter(
                [
                    "host" => $config->database->host,
                    "username" => $config->database->username,
                    "password" => $config->database->password,
                    "dbname" => $config->database->dbname
                ]
            );
        };
    }
}
