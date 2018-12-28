<?php
namespace  Qd\Utils\ThirdLib;

use Qd\Utils\Request\Curl;
/**
 * SendCloud 邮件发送类
 * luojianglai
 * @date 2018-08-22 17:56
 */

// namespace Common\Library;

/**
 * $logFile 和 debug 属性
 * 是为了兼容其他发邮件接口
 */
class SendCloudEmail
{
    // 发送数据
    private $data = null;

    // 发送地址
    private $sendUrl = 'http://api.sendcloud.net/apiv2/mail/send';

    private $errorMsg = '';

    public function __construct($config)
    {
        $this->data = array(
            // API_USER
            "apiUser" => isset($config['apiUser']) ? $config['apiUser'] : '',
            // API_KEY
            "apiKey" => isset($config['apiKey']) ? $config['apiKey'] : '',
            // 发件人地址. from 和发信域名, 会影响是否显示代发
            "from" => isset($config['apiKey']) ? $config['from'] : '',
            // 发件人名称. 显示如: ifaxin客服支持 <support@ifaxin.com>
            "fromName" => isset($config['fromName']) ? $config['fromName'] : '',
            // 收件人地址. 多个地址使用';'分隔, 如 ben@ifaxin.com;joe@ifaxin.com
        );
    }

    /**
     * 发送邮件
     * @param array $sendData 发送数据
     * @return bool
     */
    public function send($sendData)
    {
        if(!isset($sendData['to'])){
            $this->errorMsg = '收件人不能为空';
            return false;
        }
        try {
            $data = array_merge($this->data, $sendData);
            $response = Curl::post($this->sendUrl, $data, null, 'ARRAY');
            $ret = json_decode($response,true);
            if (isset($ret['statusCode']) && $ret['statusCode'] == 200) {
                return true;
            } else {
                $this->errorMsg = issset($ret['message']) ? $ret['message'] : '请求失败';
                return false;
            }

        }catch (\Exception $e) {
            $this->errorMsg = $e->getMessage();
            return false;
        }
    }

}
