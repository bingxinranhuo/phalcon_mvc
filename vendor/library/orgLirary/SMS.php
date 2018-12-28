<?php

namespace Qd\Utils\Qiaoda;

/**
 * 使用巧达短信平台发短信
 * Copyright (C) qiaodata.com 2018 All rights reserved
 * @author luojianglai
 * @date   2018/8/31 19:19
 */
use Qd\Utils\Request\Curl;
class SMS
{
    private $params     = array();
    private $sendUrl ='';
    public static $errorMsg ='';

    /**
     * 初始化配置参数
     * @param  array $config
     */
    public function __construct($config)
    {
        $this->params['channel_id']  = isset($config['channel_id']) ? $config['channel_id'] : '';
        $this->params['sign_id'] = isset($config['sign_id']) ?  $config['sign_id'] : '';
        $this->params['task_name'] = isset($config['task_name']) ? $config['task_name'] : '';
        $this->params['type'] =  isset($config['type']) ? $config['type'] : '';
        $this->params['project_id'] = isset($config['project_id'])  ? $config['project_id'] : '';
        $this->params['send_type'] =  isset($config['send_type']) ? $config['send_type'] : '';
        $this->params['token'] =  isset($config['token']) ? $config['token'] : '';
        $this->sendUrl = isset($config['sendUrl']) ? $config['sendUrl'] : '';
    }

    public function send($mobile, $content )
    {
        if(empty($mobile) || empty($content)){
            self::$errorMsg = '手机号或者短信内容为空';
            return false;
        }
        if(empty($this->sendUrl)){
            self::$errorMsg = '发送地址为空';
            return false;
        }
        $params = $this->params;
        $params['send_contents'] = [
            ['mobile'=>$mobile,'content'=>$content]
        ];

        $response = Curl::post($this->sendUrl, $params);
        if($response){
            return $response = json_decode($response,true);
        }

        self::$errorMsg = Curl::$errorMsg;
        return false;
    }

}