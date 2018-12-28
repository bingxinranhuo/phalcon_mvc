<?php
namespace Qd\Utils\Request;

    /**
     * @author: luojianglai
     * @Date:   2018-09-28
     * 当获取结果失败 比如超时 或者对方接口报错 返回false 获取错误信息:Crul::$errorMsg
     */

// define('HTTP_TIME_OUT',-3);
use  Qd\Utils\Request\Response;

class Curl
{
    public static $timeOut = 0;//毫秒
    public static $httpVersion = '1.1';
    private static $httpResponseHeader;
    private static $httpResponseBody;
    private static $defaultHeaders = [
        'Pragma' => "no-cache",
        'Cache-Control' => "no-cache",
        'Connection' => "close"
    ];
    public static $errorMsg = '';

    /**
     * http get 请求
     * @param      $url
     * @param null $headers
     * @param null $dataFormat 格式化数据 ARRAY jSON
     * @return bool|string
     * @author luojianglai
     * @date   2018/9/28 18:44
     */
    public static function get($url, $headers = null, $dataFormat = '')
    {
        try {
            $ret = self::resuest('get', $url, $headers, null);
            if (!empty($dataFormat)) {
                $ret = Response::FormatData($ret, $dataFormat);
            }
            return $ret;
        } catch (\Exception $e) {
            self::$errorMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * http post 请求
     * @param      $url
     * @param      $data
     * @param null $headers
     * @return bool|string
     * @author luojianglai
     * @date   2018/9/28 18:44
     */
    public static function post($url, $data, $headers = null, $dataFormat = '')
    {
        try {
            $ret = self::resuest('post', $url, $headers, $data);
            if (!empty($dataFormat)) {
                $ret = Response::FormatData($ret, $dataFormat);
            }
            return $ret;
        } catch (\Exception $e) {
            self::$errorMsg = $e->getMessage();
            return false;
        }
    }

    /**
     *  curl批量并发请求
     * @param $nodes  请求地海  ARRAY
     * @return bool|mixed
     */
    public function mGet($params, $headers = null)
    {
        if(empty($params) && !is_array($params)){
            self::$errorMsg = '请求参数错误';
            return false;
        }
        try {
            return self::mResuest('get', $params, $headers);
        } catch (\Exception $e) {
            self::$errorMsg = $e->getMessage();
            return false;
        }
    }

    public function mPost($params, $headers){
        if(empty($params) && !is_array($params)){
            self::$errorMsg = '请求参数错误';
            return false;
        }
        try {
            return self::mResuest('post', $params, $headers);
        } catch (\Exception $e) {
            self::$errorMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * 批量请求
     */
    public static function mResuest($method, $params, $headers = null, $data = null){
        $headers = array_merge(self::$defaultHeaders, (array)$headers);
        $setHeaders = [];
        foreach ((array)$headers as $k => $v) {
            $setHeaders[] .= $k . ': ' . $v;
        }

        $mh = curl_multi_init();
        $curlArray = array();
        foreach ($params as $i => $param) {
            $url = $param["url"];
            $curlArray[$i] = curl_init($url);


            if($method === "post"){
                $data = $param["params"];
                curl_setopt($curlArray[$i], CURLOPT_POST, true);
                curl_setopt($curlArray[$i], CURLOPT_POSTFIELDS, $data ); #post方式
            }

            if ($setHeaders) {
                curl_setopt($curlArray[$i], CURLOPT_HTTPHEADER, $setHeaders);
            }
            curl_setopt($curlArray[$i], CURLOPT_TIMEOUT_MS, self::$timeOut); //超时时间200毫秒
            //curl_setopt($curlArray[$i], CURLOPT_TIMEOUT, self::$timeOut);
            curl_setopt($curlArray[$i], CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($mh, $curlArray[$i]);
        }

        $running = NULL;
        do {
            usleep(10000);
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $res = [];
        foreach ($params as $i => $url) {
            $res[$i] = curl_multi_getcontent($curlArray[$i]);
            $errstr = curl_error($curlArray[$i]);
            self::$errorMsg .= $errstr;
        }
        foreach ($params as $i => $url) {
            curl_multi_remove_handle($mh, $curlArray[$i]);
        }
        curl_multi_close($mh);
        return $res;
    }

    /**
     * @param $metod 请求方式
     * @param $url 请求地址
     * @param null $headers
     * @param null $data
     * @return bool|mixed
     */
    public static function resuest($metod, $url, $headers = null, $data = null)
    {
        $headers = array_merge(self::$defaultHeaders, (array)$headers);
        $setHeaders = [];
        foreach ((array)$headers as $k => $v) {
            $setHeaders[] .= $k . ': ' . $v;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::$timeOut);
        //curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeOut);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_TCP_NODELAY, true);
        if ($data) {
            if (is_string($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }

        if ($setHeaders) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $setHeaders);
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, self::$httpVersion);
        curl_setopt($ch, CURLOPT_POST, $metod == 'post');

        $httpResponseBody = curl_exec($ch);
        $errStr = curl_error($ch);
        $httpResponseHeader = curl_getinfo($ch);
        curl_close($ch);
        self::setResponseBody($httpResponseBody);
        self::setResponseHeader($httpResponseHeader);
        if ($httpResponseHeader['http_code'] != 200) {
            self::$errorMsg = $errStr;
            return false;
        } else {
            return $httpResponseBody;
        }
    }

    /**
     * 获取返回内容
     * @author luojianglai@qiaodata.com
     * @date   2018/9/30 11:31
     */
    public static function getResponseBody()
    {
        return self::$httpResponseBody;
    }

    /**
     * 获取相应头
     * @author luojianglai@qiaodata.com
     * @date   2018/9/30 11:31
     */
    public static function getResponseHeader()
    {
        return self::$httpResponseHeader;
    }

    /**
     * 设置返回内容
     * @author luojianglai@qiaodata.com
     * @date   2018/9/30 11:30
     */
    private static function setResponseBody($httpResponseBody)
    {
        self::$httpResponseBody = $httpResponseBody;
    }

    /**
     * 设置响应头
     * @author luojianglai@qiaodata.com
     * @date   2018/9/30 11:30
     */
    private static function setResponseHeader($httpResponseHeader)
    {
        self::$httpResponseHeader = $httpResponseHeader;
    }
}
