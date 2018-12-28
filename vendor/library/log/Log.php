<?php
namespace Qd\Utils\Log;

//兼容php各版本
if (!function_exists('posix_getpid')) {
    function posix_getpid()
    {
        return getmypid();
    }
}

/**
 * @author: luojianglai
 * @Date:   2018-09-28
 * File name: Log.php
 * Class name: 日志记录类
 * Create date: 2018/09/27
 * Description: 日志记录类
 */
class Log
{
    private static $logPath;           //日志路径包括文件名
    private static $logLevel;        //日志的写入级别  debug > info > notice > warning > error
    public static $logPid;           //进程号
    private static $logId;         //日志唯一标识id
    private static $noticeStr;     //追加notice日志

    //日志类型
    const HOUR_ROLLING = 1;
    const DAY_ROLLING = 2;
    const MONTH_ROLLING = 3;

    //日志级别
    const LOG_ERROR = 1;
    const LOG_WARNING = 2;
    const LOG_NOTICE = 4;
    const LOG_INFO = 8;
    const LOG_DEBUG = 16;

    /**
     *
     */
    public static function generateLogId()
    {

    }

    /**
     * @param string $path 日志路径 例/a/b
     * @param string $name 日志文件名 例 error info
     * @param int $level 日志级别   低于设定级别的日志不会被记录 error级别写入error文件 其他写入access文件
     * @param string $logId 日志唯一标识
     * @param string $rollType 日志文件类别 1:YmdH 2:Ymd 3:Ym 其他: .log
     */
    public static function init($path, $level = self::LOG_INFO, $logId = '', $rollType = self::DAY_ROLLING)
    {
        if (empty($path)) {
            die('日志目录及文件名不能为空');
        }
        if (!is_writable($path)) {
            die('日志目录不可写入');
        }

        $file = $level == self::LOG_ERROR ?  'error' : 'access' ;
        $prefix = rtrim($path, '/') . '/' . $file;
        switch ($rollType) {
            case self::DAY_ROLLING:
                $prefix .=  date('Ymd') . '.log';
                break;
            case self::MONTH_ROLLING:
                $prefix .=  date('Ym') . '.log';
                break;
            case self::HOUR_ROLLING:
                $prefix .=  date('YmdH') . '.log';
                break;
            default:
                $prefix .=  '.log';
                break;
        }
        self::$logPath = $prefix;
        self::$logLevel = $level;
        self::$logPid = posix_getpid();
        self::$logId = empty($logId) ? md5(microtime() . posix_getpid() . uniqid()) : $logId;
    }

    public function __destruct()
    {
    }

    /**
     * @param string|array $msg
     */
    public static function error($msg)
    {
        self::writeLog(self::LOG_ERROR, $msg);
    }

    /**
     * @param string|array $msg
     */
    public static function warning($msg)
    {
        self::writeLog(self::LOG_WARNING, $msg);
    }

    /**
     * @param string|array $msg
     */
    public static function notice($msg)
    {
        self::writeLog(self::LOG_NOTICE, $msg);
    }

    /**
     * @param string|array $msg
     */
    public static function info($msg)
    {
        self::writeLog(self::LOG_INFO, $msg);
    }

    /**
     * @param string|array $msg
     */
    public static function debug($msg)
    {
        self::writeLog(self::LOG_DEBUG, $msg);
    }


    /**
     * 追加nontice日志
     * @param $format
     * @param $arr_data
     */
    public static function pushNotice($msg)
    {
        if (is_array($msg)) {
            self::$noticeStr .= " " . json_encode($msg);
        } else {
            self::$noticeStr .= " " . $msg;
        }

    }

    /**
     * 写入日志
     * @param  int $level
     * @param string|array $msg
     */
    private static function writeLog($level, $msg)
    {
        if ($level < self::$logLevel ) {//低于设定级别的日志不记录
            return;
        }

        $logLevelName = [1 => 'error', 2 => 'warning', 4 => 'info', 8 => 'info', 16 => 'debug'];
        $lineNo = self::getLineNo();
        $micro = microtime();
        $pos = strpos($micro, " ");
        $sec = intval(substr($micro, $pos + 1));
        $ms = floor(substr($micro, 0, $pos) * 1000000);
        $str = sprintf(
            "%s.%-06d: [%s]: %s: * %d %s",
            date("Y-m-d H:i:s", $sec),
            $ms,
            $logLevelName[$level],
            self::$logId,
            posix_getpid(),
            $lineNo
        );

        if (is_array($msg)) {
            $str .= json_encode($msg);
        } else {
            $str .= $msg;
        }

        if (!empty(self::$noticeStr) && $level == self::LOG_NOTICE) {
            $str .= self::$noticeStr;
        }
        $str .= "\n";

        $fd = @fopen(self::$logPath, "a+");
        if (is_resource($fd)) {
            fputs($fd, $str);
            fclose($fd);
        }
        return;
    }

    /**
     * 获取去文件行号
     */
    public static function  getLineNo()
    {
        $bt = debug_backtrace();
        if (isset($bt[1]) && isset($bt[1] ['file'])) {
            $c = $bt[1];
        } else {
            if (isset($bt[2]) && isset($bt[2] ['file'])) { //为了兼容回调函数使用log
                $c = $bt[2];
            } else {
                if (isset($bt[0]) && isset($bt[0] ['file'])) {
                    $c = $bt[0];
                } else {
                    $c = array('file' => 'faint', 'line' => 'faint');
                }
            }
        }
        return '[' . $c ['file'] . ':' . $c ['line'] . '] ';
    }

}
