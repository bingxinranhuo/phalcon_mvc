<?php
/**
 * 引导程序
 * @author luojianglai
 */

use Phalcon\Logger\Adapter\File as FileAdapter;

//容器
$di = new Phalcon\Di\FactoryDefault();
//日志
$di->setShared('logger', function () {
    $date = date('Ymd');
    $logger = new FileAdapter(LOG_PATH . "{$date}.log");
    return $logger;
});
//报错记录日志
register_shutdown_function(function () use ($di) {
    error_reporting(0);
    $msg = error_get_last();
    if (empty($msg)) {
        return;
    }

    $content = 'url:' . $_SERVER['REQUEST_URI']
        . ' file:' . $msg['file']
        . ' line:' . $msg['line']
        . ' message:' . $msg['message'];

    if (isset($_SERVER['HTTP_COOKIE'])) {
        $offset = strpos($_SERVER['HTTP_COOKIE'], 'fid=');
        if ($offset !== false) {
            $cookie = substr($_SERVER['HTTP_COOKIE'], $offset + 4);
            $content .= ' cookie:' . $cookie;
        }
    }

    //根据PHP 内部编号区分错误等级
    switch ($msg['type']) {
        case 2:
        case 8:
        case 32:
        case 128:
        case 512:
        case 1024:
            $di->getShared('logger')->warning($content);
            break;
        case 1:
        case 4:
        case 16:
        case 64:
        case 256:
            $di->getShared('logger')->error($content);
//            if (DEV_MODEL != 'dev') {
//                include 'inform.php';
//                sendMail($content);
//            }
            if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {
                echo file_get_contents(ROOT_PATH . 'home/views/common/500.html');
            } else {
                echo json_encode(['code' => 9999, 'msg' => '系统错误,请联系客服']);
            }
            break;
        default:
            $di->getShared('logger')->error($content);
//            if (DEV_MODEL != 'dev') {
//                include 'inform.php';
//                sendMail($content);
//            }
            if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {
                echo file_get_contents(ROOT_PATH . 'home/views/common/500.html');
            } else {
                echo json_encode(['code' => 9999, 'msg' => '系统错误,请联系客服']);
            }
            break;
    }
    return;
});

//注册路径
$loader = new Phalcon\Loader();
$loader->registerNamespaces([
    'home\controllers' => ROOT_PATH . 'home/controllers/',
])->register();


$di->set('dispatcher', function () {
    $dispatcher = new Phalcon\Mvc\Dispatcher();
    $dispatcher->setDefaultNamespace('home\controllers\\');
    return $dispatcher;
});
//配置
$di->setShared('config', function () {
    return include CONF_PATH . 'config.php';
});
//视图
$di->set('view', function () {
    $view = new \Phalcon\Mvc\View();
    $view->setViewsDir(ROOT_PATH . 'home/views/');
    $view->registerEngines([
        ".html" => 'voltService',
    ]);
    return $view;
});
//视图引擎
$di->set('voltService', function ($view, $di) {
    $volt = new Phalcon\Mvc\View\Engine\Volt($view, $di);
    $volt->setOptions([
        "compileAlways" => true,
        'compiledPath' => TMP_PATH . 'cache/'

    ]);
//        $volt->getCompiler();
    return $volt;
});
//数据库
$di->setShared('db', function () {
    $dbconfig = $this['config']['database'];
    if (!is_array($dbconfig) || count($dbconfig) == 0) {
        throw new \Exception("the database config is error");
    }

    $connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
            'host' => $dbconfig['host'],
            'port' => $dbconfig['port'],
            'username' => $dbconfig['username'],
            'password' => $dbconfig['password'],
            'dbname' => $dbconfig['dbname'],
            'charset' => $dbconfig['charset'],
        )
    );

    if (DEBUG) {
        // 分析底层sql性能，并记录日志
        $profiler = new \Phalcon\Db\Profiler();
        $eventsManager = new \Phalcon\Events\Manager();
        $eventsManager->attach('db', function ($event, $connection) use ($profiler) {
            if ($event->getType() == 'beforeQuery') {
                //在sql发送到数据库前启动分析
                $profiler->startProfile($connection->getSQLStatement());
            }
            if ($event->getType() == 'afterQuery') {
                //在sql执行完毕后停止分析
                $profiler->stopProfile();
                //获取分析结果
                $profile = $profiler->getLastProfile();
                $sql = $profile->getSQLStatement();
                $executeTime = $profile->getTotalElapsedSeconds();
                //日志记录
                $logger = new FileAdapter(LOG_PATH . date('Ymd') . ".log");
                $logger->debug("{$sql} {$executeTime}");
            }
        });
        /* 注册监听事件 */
        $connection->setEventsManager($eventsManager);
    }
    return $connection;
});
//数据模型管理器
$di->set('modelsManager', function () {
    return new Phalcon\Mvc\Model\Manager();
});
//redis
$di->setShared("redis", function () {
    $redis = new \Redis();
    $redis->connect($this['config']['redis']['host'], $this['config']['redis']['port']);
    $redis->auth($this['config']['redis']['auth']);
    return $redis;
});
//路由
$di->set('router', function () {
    $Router = new \Phalcon\Mvc\Router();
    $Router->removeExtraSlashes(true);
    return $Router;
});
//模型缓存
$di->set('modelsCache', function () {
    $frontCache = new Phalcon\Cache\Api\Data([
        "lifetime" => 86400
    ]);
    $cache = new Phalcon\Cache\Backend\Redis(
        $frontCache,
        [
            "host" => $this['config']['redis']['host'],
            "port" => $this['config']['redis']['port'],
            "auth" => $this['config']['redis']['auth'],
            "persistent" => false,
            "index" => 0,
        ]);
    return $cache;
});
