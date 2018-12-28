<?php
namespace  Qd\Utils\File;

/**
 * 文件类操作
 * @author: luojianglai
 * @Date:   2018-09-28
 */
class File
{
    public static $errorMsg;

    /**
     * 创建指定路径下的指定文件
     * @param string $path (需要包含文件名和后缀)
     * @param boolean $over_write 是否覆盖文件
     * @param int $time 设置时间。默认是当前系统时间
     * @param int $atime 设置访问时间。默认是当前系统时间
     * @return boolean
     */
    public function createFile($path, $over_write = FALSE, $time = NULL, $atime = NULL)
    {
        if(empty($path)){
            self::$errorMsg = '文件路径为空';
            return false;
        }
        try {
            $path = $this->dirReplace($path);
            $time = empty($time) ? time() : $time;
            $atime = empty($atime) ? time() : $atime;
            if (file_exists($path) && $over_write) {
                $this->unlinkFile($path);
            }
            $aimDir = dirname($path);
            $this->createDir($aimDir);
            return touch($path, $time, $atime);
        } catch (\Exception $e) {
            self::$errorMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * 写入文件内容
     * @param string $path (需要包含文件名和后缀)
     */
    public function writeFile($path, $content, $flag = 1)
    {
        try {
            if ($flag) {//追加写入
                file_put_contents($path, $content, FILE_APPEND);
            } else {
                file_put_contents($path, $content);
            }
        } catch (\Exception $e) {
            self::$errorMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * 创建多级目录
     * @author: luojianglai
     * @Date:   2018-09-28
     * @return boolean
     */
    public function createDir($dir, $mode = 0777)
    {
        return is_dir($dir) or ($this->createDir(dirname($dir)) and mkdir($dir, $mode));
    }

    /**
     * 删除文件
     * @param string $path
     * @return boolean
     */
    public function unlinkFile($path)
    {
        $path = $this->dirReplace($path);
        if (file_exists($path)) {
            return unlink($path);
        }
    }


    /**
     * 替换相应的字符
     * @param string $path 路径
     * @return string
     */
    public function dirReplace($path)
    {
        return str_replace('//', '/', str_replace('\\', '/', $path));
    }

}


?>
