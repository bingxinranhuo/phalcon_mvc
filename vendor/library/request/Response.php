<?php
namespace Qd\Utils\Request;

class Response
{

    /**
     * Ajax方式返回数据到客户端
     * @param $err 状态码 0:成功
     * @param $msg 状态文案描述
     * @param $res 返回的数据
     * @param $logId 请求id
     * @param string $dataType 返回数据类型  JSON XML JSONP EVAL
     * luojianglai
     * @date 2018-08-17 11:01
     */
    public static function ajaxReturn( $res = [] , $err = 0, $msg = 'success' , $logId, $dataType = 'JSON'){
        $return = ['err' => $err, 'msg' => $msg, 'res' => $res, 'logId' => $logId];
        echo self::FormatData($return, $dataType);
    }
    /**
     * Ajax方式返回数据到客户端 err msg res logId
     * @param $data 返回的数据
     * @param string $type 返回数据类型
     * @param int $code 状态码
     * @param int $jsonOption 传递给json_encode的option参数
     * luojianglai
     * @date 2018-08-17 11:01
     */
    public static function FormatData($data = [], $type = 'JSON')
    {
        switch (strtoupper($type)) {
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                return json_encode($data);
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                return self::xmlEncode($data);
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler = isset($_GET['callback']) ? $_GET['callback'] : 'jsonpReturn';
                return $handler . '(' . json_encode($data) . ');';
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                return $data ;
            case 'ARRAY':
                header('Content-Type:text/html; charset=utf-8');
                $ret = json_decode($data, true);
               if (json_last_error() != JSON_ERROR_NONE) {
                   $ret = json_decode(json_encode($data), true);
               }
               return $ret;
            default:
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                return json_encode($data);
        }
    }

    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id 数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     * luojianglai
     * @date 2018-08-17 11:01
     */
    public static function xmlEncode($data, $root = 'think', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8')
    {
        if (is_array($attr)) {
            $attrItem = array();
            foreach ($attr as $key => $value) {
                $attrItem[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $attrItem);
        }
        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml .= "<{$root}{$attr}>";
        $xml .= self::dataToXml($data, $item, $id);
        $xml .= "</{$root}>";
        return $xml;
    }

    /**
     * 数据xml编码
     * @param mixed $data 数据
     * @param string $item 数字索引时的节点名称
     * @param string $id 数字索引key转换为的属性名
     * @return string
     * luojianglai
     * @date 2018-08-17 11:01
     */
    public static function dataToXml($data, $item = 'item', $id = 'id')
    {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && $attr = " {$id}=\"{$key}\"";
                $key = $item;
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= (is_array($val) || is_object($val)) ? self::dataToXml($val, $item, $id) : $val;
            $xml .= "</{$key}>";
        }
        return $xml;
    }

}



