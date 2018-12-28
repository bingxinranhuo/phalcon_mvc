<?php
/**
 * Copyright (C) qiaodata.com 2018 All rights reserved
 * @author luojianglai
 * @date   2018/12/24 18:20
 */
return [
    'database' => [
        'adapter' => 'Mysql',
        "host" => "10.18.99.144",
        "port" => "3306",
        "username" => "wangjianghua",
        "password" => "Wjh@a_test#&12a!",
        'dbname' => 'phalcon',
        "charset" => "utf8",
    ],
    'application' => [
        'controllersDir' => __DIR__ . '/../controllers/',
        'modelsDir' => __DIR__ . '/../models/',
        'viewsDir' => __DIR__ . '/../views/',
        'libraryDir' => __DIR__ . '/../library/',
        'pluginsDir' => __DIR__ . '/../plugin/',
        'baseUri' => '/'
    ]
];

