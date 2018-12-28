<?php
namespace  Qd\Utils\ThirdLib;
/**
 * 数美滑动验证码类
 * luojianglai
 * @date: 2018/8/22 17:15
 */
use Qd\Utils\Request\Curl;
class IshumeiSverify
{
    /**
     * accessKey 配置
     */
    public static $sm_maccesskey = 'xxxxxxxxxx'; // 客户的accessKey

    /**
     * captcha api 请求的url
     */
    const SM_CAPTCHA_HOST = 'http://captcha.fengkongcloud.com/ca/v1/sverify';

    /**
     * 接口请求超时时间 毫秒
     * @var int
     */
    public $timeout = 5000;

    public function __construct($sm_maccesskey = null)
    {
        if(!empty($sm_maccesskey)){
            self::$sm_maccesskey = $sm_maccesskey;
        }

    }

        /**
     * 滑动验证码验证
     * @param $rid 滑动验证请求标识
     * @param $ip 设置用户滑动验证码时的 ip
     * @param string $tokenId 设置用户账号tokenId, 由客户提供 id
     * @param string $deviceId 设置deviceId, 由客户提供 数美设备指纹标识，由于用户行为分析
     * @return array
     * luojianglai
     * @date: 2018/8/22 17:15
     */
    public function verify($rid, $ip, $tokenId = '', $deviceId = '')
    {
        try {

            $data = [];

            // 滑动验证请求标识
            if (empty($rid)) {
                return ['code' => 1, 'msg' => '验证失败,rid是必须的！', 'data' => []];
            } else {
                $data['rid'] = $rid;
            }

            // 设置用户滑动验证码时的 ip
            if (empty($ip)) {
                return ['code' => 1, 'msg' => '验证失败,ip是必须的！', 'data' => []];
            } else {
                $data['ip'] = $ip;
            }

            // 设置用户账号tokenId, 由客户提供 id
            if (!empty($tokenId)) {
                $data['tokenId'] = $tokenId;
            }

            // 设置deviceId, 由客户提供 数美设备指纹标识，由于用户行为分析
            if (!empty($deviceId)) {
                $data['deviceId'] = $deviceId;
            }

            // 发起验证请求
            $postData['accessKey'] = self::SM_ACCESSKEY;
            $postData['data'] = $data;
            //设置超时
            Curl::$timeout = $this->timeout;
            $resJson = Curl::Post(self::SM_CAPTCHA_HOST, $postData, null, 'ARRAY'); // 发起接口请求

            /*
            接口会返回code， code=1100 时说明请求成功，根据不同的 riskLevel 风险级别进行业务处理
            当 code!=1100 时，如果是 1902 错误，需要检查参数配置
            其余情况需要根据错误码进行重试或者其它异常处理
            */
            if (isset($resJson["code"]) && $resJson["code"] == 1100) {
                if ($resJson["riskLevel"] == "PASS") {
                    // 放行
                    return ['err' => 0, 'msg' => '验证通过！', 'res' => $resJson['detail']];
                } else if ($resJson["riskLevel"] == "REJECT") {
                    // 拒绝
                    return ['err' => 1, 'msg' => '验证未通过！', 'res' => $resJson['detail']];
                } else {
                    // 异常
                    return ['err' => 1, 'msg' => '验证异常！', 'res' => $resJson['detail']];
                }
            } else {
                // 接口请求失败，需要参照返回码进行不同的处理
                return ['err' => 1, 'msg' => '验证接口异常！', 'res' => $resJson];
            }
        } catch (\Exception $exception) {
            // 接口请求失败，需要参照返回码进行不同的处理
            return ['err' => 1, 'msg' => '验证失败,系统错误！' . $exception->getMessage(), 'res' => []];
        }

    }

}

