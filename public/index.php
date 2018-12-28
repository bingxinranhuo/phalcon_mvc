<?php

use Phalcon\Mvc\Application;

define('DEBUG', true);
define('ENVIRONMENT', 'local'); //影响config路径下的配置文件载入,dev开发模式,test测试模式,, online生产模式,
define('APP_PATH', dirname(__DIR__) . '/apps/');
define('CONF_PATH', dirname(__DIR__) . '/config/');
define('MODULES', 'frontend'); //模块
define('LOG_PATH', '/home/work/logs/project_name');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {

    /**
     * Include services
     */
    require APP_PATH . 'modules/' . MODULES . '/bootstrap/services.php';

    /**
     * Handle the request
     */
    $application = new Application();

    /**
     * Assign the DI
     */
    $application->setDI($di);

    /**
     * Include modules
     */
    require APP_PATH . 'modules/' . MODULES . '/bootstrap/modules.php';

    echo $application->handle()->getContent();
} catch (Phalcon\Exception $e) {
    echo $e->getMessage();
} catch (PDOException $e) {
    echo $e->getMessage();
}
