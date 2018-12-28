<?php
/**
 * Created by PhpStorm.
 * User: wangzhan
 * Date: 2018/03/27
 * Time: 下午4:00
 */

use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Session\Adapter\Files as SessionAdapter;

/**
 * Registering a router
 */
$di->setShared('router', function () {
    $router = new Router();

    $router->setDefaultModule('backend');

    return $router;
});

/**
 * The URL component is used to generate all kinds of URLs in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();

    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Starts the session the first time some component requests the session service
 */
$di->setShared('session', function () {
    $session = new SessionAdapter();
    $session->start();

    return $session;
});

/**
 * Set the default namespace for dispatcher
 */
$di->setShared('dispatcher', function() {
    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace('Backend\Controllers');
    return $dispatcher;
});

$di->set('crypt', function() {

    $crypt = new Phalcon\Crypt();

    //设置全局加密密钥
    $crypt->setKey('%31.1e$i86e$f!8jz');

    return $crypt;
}, true);