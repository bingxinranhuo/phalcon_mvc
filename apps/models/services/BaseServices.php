<?php
/**
 * Created by PhpStorm.
 * User: wangzhan
 * Date: 2018/03/28
 * Time: 下午2:29
 */

namespace Common\Services;

use Phalcon\Di;

/**
 * Class BaseServices
 * 基础service 定义一些需要初始化的信息
 * @package Services
 * @property \Phalcon\Di $di Di变量;
 * @property \Phalcon\Logger\Adapter $logger;
 */
use Phalcon\Mvc\Application;

class BaseServices extends Application
{
    protected $di;
    protected $errorMsg;
    protected $logger;


    //用于setConnect方法
    protected $_loopTime;

    public function __construct()
    {
        $this->di = Di::getDefault();
        $this->_loopTime = time();
    }

    public function getErr()
    {
        return $this->errorMsg;
    }


    /**
     * 用于和数据库重新建立连接
     * isCon = false   1分钟建立一次
     * @param bool $isCon
     */
    public function setConnect($isCon = false){
        if(time() - $this->_loopTime > 60 || $isCon) {
            $di = Di::getDefault();
            $config = $di->get('config');
            $class  = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
            $params = [
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->dbname,
                'charset'  => $config->database->charset
            ];
            $connection = new $class($params);

            $di->remove('db');
            $di->set('db', $connection);
        }
    }

    /**
     * 重新连接redis
     */
    public function setConnectRedis(){
        $di = Di::getDefault();
        $config = $di->get('config');

        \Predis\Autoloader::register();
        $redisClient = new \Predis\Client($config->redis_cluster->Toarray(),$config->redis_options->Toarray());
        $di->remove('redis');
        $di->set('redis', $redisClient);
    }


}