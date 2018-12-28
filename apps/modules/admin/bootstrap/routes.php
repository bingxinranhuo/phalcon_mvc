<?php
/**
 * Created by PhpStorm.
 * User: wangzhan
 * Date: 2018/03/27
 * Time: 下午4:11
 */

$router = $di->getRouter();

foreach ($application->getModules() as $key => $module) {

    $namespace = preg_replace('/Module$/', 'Controllers', $module["className"]);

    $router->add('/'.$key.'/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 'index',
        'action' => 'index',
        'params' => 1
    ])->setName($key);

    $router->add('/'.$key.'/:controller/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 'index',
        'params' => 2
    ]);
    if($key!="backend"){
        $router->add('/'.$key.'/:controller/:action/:params', [
            'namespace' => $namespace,
            'module' => $key,
            'controller' => 1,
            'action' => 2,
            'params' => 3
        ])->convert('action', function($action) {
            $routeslist = new \Phalcon\Config\Adapter\Php(APP_PATH . "/config/routeslist.php");
            $routeslist = $routeslist->toArray();
            if(isset($routeslist[$action])) {
                return $routeslist[$action];
            }else{
                //return $action;
                echo "异常请求";die();
            }
        });
    }else{
        $router->add('/'.$key.'/:controller/:action/:params', [
            'namespace' => $namespace,
            'module' => $key,
            'controller' => 1,
            'action' => 2,
            'params' => 3
        ]);
    }
}


