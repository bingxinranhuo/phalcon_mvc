<?php
/**
 * Copyright (C) qiaodata.com 2018 All rights reserved
 * @author luojianglai
 * @date   2018/5/17 21:56
 */

$router = $di->getRouter();
//    $router->add('/?([a-zA-Z0-9_-]*)/?([a-zA-Z0-9_]*)(/.*)*', [
//        'namespace' => 'home\controllers',
//        'controller' => 1,
//        'action' => 2,
//        'params' => 3
//    ]);

$router->notFound([
    "controller" => "index",
    "action" => "notFound"
]);

$router->add(
    '/',
    [
        'controller' => 'index',
        'action' => 'index'
    ]
);


$router->handle();

