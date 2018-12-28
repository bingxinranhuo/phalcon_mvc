<?php

error_reporting(0);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require_once BASE_PATH . '/vendor/autoload.php';

use Phalcon\Di;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;

use Workerman\Lib\Timer;
use Workerman\Worker;
use Workerman\Connection\TcpConnection;

try {

    //获取日志目录
    $config = include_once APP_PATH . "/config/config.php";
    $logPath = $config['web_log'];
    if(!is_dir($logPath)){
        mkdir($logPath, 0777, true);
    }

    //连接句柄
    $clients = []; //保存客户端信息

    //保存已经推送的消息ID，避免重复推送
    $sendNoticeIds = [];

    // 设置所有连接的默认应用层发送缓冲区大小
    TcpConnection::$defaultMaxSendBufferSize = 4 * 1024 * 1024;

    //保存pid文件
    Worker::$pidFile = $logPath . 'workerman_websocket.pid';

    //服务启动日志
    Worker::$logFile = $logPath . 'workerman_websocket_log.log';

    //输出日志的文件
    Worker::$stdoutFile = $logPath . 'workerman_websocket_stdout.log';

    //以守护进程运行
    Worker::$daemonize = true;

    // 创建一个Worker，使用websocket协议通讯
    $worker = new Worker('websocket://0.0.0.0:8170');

    $worker->user = 'work';

    // 启动4个进程对外提供服务
    $worker->count = 4;

    $noticeService = NULL;

    //用于判断推送的
    $startTime = time();

    $worker->onWorkerStart = function($worker) {

        global $noticeService;

        $di = new FactoryDefault();

        require_once APP_PATH . '/config/services.php';

        $config = $di->getConfig();

        include_once APP_PATH . '/config/loader.php';

        $application = new Application($di);

        $noticeService = new \Common\Services\NoticeServices();

        //定时器，每秒执行一次,有新的消息就进行推送
        Timer::add(1, function(){
            global $clients, $noticeService, $startTime, $sendNoticeIds;

            //轮询redis,查看是否有消息
            $endTime = time();
            $noticeId = $noticeService->getMark($startTime, $endTime);
            $startTime = $endTime;
            $logContent = '';


            //加入心跳逻辑
            if(time() % 30 == 0){
                $ping = json_encode(['count' => 0, 'action' => 'ping']);
                if(isset($clients['browser']) && !empty($clients['browser'])) {
                    foreach ($clients['browser'] as $con) {
                        $con->send($ping);
                    }
                }
                if(isset($clients['web']) && !empty($clients['web'])) {
                    foreach ($clients['web'] as $con) {
                        $con->send($ping);
                    }
                }
            }

            //防止重复发送
            if($noticeId < 1 || in_array($noticeId, $sendNoticeIds)){
                return ;
            }
            array_push($sendNoticeIds, $noticeId);
            //保持已经发送的ID长度不大于50
            if(count($sendNoticeIds) > 50){
                array_pop($sendNoticeIds);
            }

            if(!empty($clients)){

                //推送给浏览器的消息
                $info = $noticeService->tryGetNoticeInfo($noticeId,  $logContent);

                if(!empty($info) and !empty($clients['browser'])){
                    //推送给浏览器
                    $noticeInfo = [
                        'count' => 1,
                        'data' => [$info['id'] => $info],
                    ];
                    $noticeInfo = json_encode($noticeInfo);

                    foreach($clients['browser'] as $con){
                        $uid = $con->uid;
                        $re = $noticeService->pushNotice($uid, $noticeId, strtotime($info['create_date']));

                        $re = $con->send($noticeInfo);

                        $re = var_export($re, true);

                        $logContent .= "browser:给 id = " . $con->id . ", uid = " . $con->uid .", 发送结果: $re,  推送消息" . $noticeInfo;

                    }
                }

                //给web推送消息
                if(!empty($info) and $info['notice_type'] == 'notify' and !empty($clients['web'])){

                    foreach($clients['web'] as $con){
                        $gtDate = $con->user_create_date;
                        $uid = $con->uid;
                        $list = $noticeService->getTop($gtDate, 3, $uid, 'notify');

                        $list = array_values($list);
        
                        $noticeInfo = [
                            'count' => count($list),
                            'data' => $list,
                        ];
                        $noticeInfo = json_encode($noticeInfo);

                        $re = $con->send($noticeInfo);

                        $re = var_export($re, true);

                        $logContent = "web:给 id = " . $con->id . ", uid = " . $con->uid .", 发送结果: $re, 推送消息" . $noticeInfo;

                        \Common\Services\LogService::info($logContent);
                    }
                }

            }
            if($logContent){
                \Common\Services\LogService::info($logContent);
            }


        });

        //捕获一些系统异常，错误消息
        $logService = new \Common\Services\LogService("系统错误，警告等日志־", 'sys');
        register_shutdown_function([$logService, 'sysError']);

        //worker启动日志
        $logContent = "worker启动 : worker_id=" . $worker->workerId . ", id = " . $worker->id;
        \Common\Services\LogService::info($logContent);

    };

    /**
     * 连接进入
     * @param $connection
     */
    $worker->onConnect = function($connection) {

        $logContent = "客户端进入 : id=" . $connection->id;
        $logContent .= ",address=" . $connection->getRemoteIp() . ":" . $connection->getRemotePort();
        \Common\Services\LogService::info($logContent);
    };


    /**
     * 有消息到达
     * @param $connection
     * @param $str
     */
    $worker->onMessage = function($connection, $str){
        global $clients, $noticeService;

        //{"action":"setUid","uid":123,"from":"browser"}   //连接后发送用户ID,  from来源  browser浏览器
        //{"count":2,"data":[{"id":"1",""notice_title:"title","notice_content":"content","notice_type","activity","notice_detail":""http://www.baidu.com},
        //{"id":"2",""notice_title:"title","notice_content":"content","notice_type","notify"}]}    推送没有读取的消息

        //{"action":"confirm","notice_ids":[1,2]}    //确认收到的消息
        //{"action":"look","notice_ids":[1,2]}      //消息被查看
        //{"action":"error","log":"log"}        //客户端出现异常的报错

        //浏览器的
        //{"action":"setToken","token":"123","from":"web"}   //连接成功后要发送的，除token值外，其他都是固定的

        /*{"count":2,"data":[{"id":"1","notice_title":"title","notice_content":"content","notice_type":"activity","notice_detail":"http://www.baidu.com","status":""},
        {"id":"2","notice_title":"title","notice_content":"content","notice_type":"notify","status":""}]}
        推送的消息，count是数量  notice_type消息类型,activity活动，notify通知，recovery恢复，sys系统通知
        status  消息的查看状态   空值没有推送，send已经推送,confirm推送到客户端后确认收到,look消息被查看

        */
        try {
            $connection->from = 'browser';
            $str = trim($str);
            $logContent = '';

            $data = json_decode($str, true);

            if ($data['action'] == 'ping') {
                return;
            }

            if ($data['action'] == 'setUid' || $data['action'] == 'setToken') {
                //连接后，需要发送用户ID，用于标识,保存用户和连接的关系

                //需要把Token解析成uid
                if ($data['action'] == 'setToken') {
                    $userInfo = \Common\Services\UserServices::dectyptToken($data['token']);
                    $data['uid'] = isset($userInfo['uid']) ? $userInfo['uid'] : 0;
                }

                $connectionId = $connection->id;
                $from = $data['from'];
                $uid = $data['uid'];

                if ($uid > 0 && !empty($from)) {

                    //用户消息
                    $userService = new \Common\Services\UserServices();
                    $userInfo = $userService->tryGetUserInfo($uid, $logContent);

                    if ($userInfo['uid'] < 1) {
                        $connection->close();
                    } else {
                        //需要推送的消息
                        if ($data['action'] == 'setToken') {
                            $list = $noticeService->getTop($userInfo['create_date'], 3, $uid, 'notify');
                            $list = array_values($list);
                        } else {
                            $list = $noticeService->getSendNotice($uid, $userInfo['create_date']);
                        }

                        if (!empty($list)) {
                            $return = [
                                'count' => count($list),
                                'data' => $list,
                            ];
                            $connection->send(json_encode($return));
                        }

                        $connection->from = $from;
                        $connection->uid = $uid;
                        $connection->user_create_date = $userInfo['create_date'];
                        $clients[$from][$connectionId] = $connection;
                    }
                } else {
                    $connection->close();
                }

            } else if ($data['action'] == 'confirm' || $data['action'] == 'look') {
                //确认收到消息，查看了消息
                if (!isset($connection->uid) || $connection->uid < 1) {
                    $connection->close();
                } else {
                    $uid = $connection->uid;

                    $noticeIds = $data['notice_ids'];

                    $status = $data['action'];

                    $noticeService->setNoticeStatus($uid, $noticeIds, $status);
                }
            } else if ($data['action'] == 'error') {
                //客户端返回的异常消息
                \Common\Services\LogService::error("返回错误异常消息 " . $data['log']);
            }

            $logContent .= "客户端发送消息 : id = " . $connection->id;
            $logContent .= ",address = " . $connection->getRemoteIp() . ":" . $connection->getRemotePort();
            $logContent .= ",data = " . $str;
            \Common\Services\LogService::info($logContent);
        }catch (\Exception $e){
            \Common\Services\LogService::info("发生异常" . json_encode($e));
        }
    };

    /**客户端主动关闭
     * @param $connection
     */
    $worker->onClose = function($connection) {
        global $clients;
        $logContent = "客户端关闭 : id = " . $connection->id;
        $logContent .= ",address = " . $connection->getRemoteIp() . ":" . $connection->getRemotePort();

        //删除保存的客户端消息
        $from = isset($connection->from) ? $connection->from : '';
        $connectionId = $connection->id;

        unset($clients[$from][$connectionId]);

        \Common\Services\LogService::info($logContent);
    };


    /**
     * 连接异常
     * @param $connection
     * @param $code
     * @param $msg
     */
    $worker->onError = function($connection, $code, $msg) {
        global $clients;

        $logContent = "异常 : id = " . $connection->id;
        $logContent .= ",address = " . $connection->getRemoteIp() . ":" . $connection->getRemotePort();
        $logContent .= ", code = $code, msg = $msg";

        //删除保存的客户端消息
        $from = isset($connection->from) ? $connection->from : '';
        $connectionId = $connection->id;

        unset($clients[$from][$connectionId]);

        \Common\Services\LogService::info($logContent);

    };
    
    /**
     *缓冲区满的时候调用
     * @param $connection
     */
    $worker->onBufferFull = function($connection) {
        $logContent = "缓冲区满 onBufferFull: id = " . $connection->id;
        $logContent .= ",address = " . $connection->getRemoteIp() . ":" . $connection->getRemotePort();
        \Common\Services\LogService::info($logContent);
    };

    /**
     * 应用层的缓冲区满
     * @param $connection
     */
    $worker->onBufferDrain = function($connection) {
        $logContent = "缓冲区满 onBufferDrain: id = " . $connection->id;
        $logContent .= ",address = " . $connection->getRemoteIp() . ":" . $connection->getRemotePort();
        \Common\Services\LogService::info($logContent);
    };

    Worker::runAll();

} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
