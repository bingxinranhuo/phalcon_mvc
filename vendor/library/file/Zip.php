<?php
namespace  Qd\Utils\File;

/**
 * 压缩文件类
 * @author: luojianglai
 * @Date:   2018-09-28
 */
class Zip extends \ZipArchive
{

    public $errorMsg;
    public $flag = self::CREATE;
    /**
     * 压缩文件下添加目录
     * @author: luojianglai
     * @Date:   2018-10-10
     */
    public function createDir($zipFile, $newDir)
    {
        if (empty($zipFile) || empty($newDir)) {
            $this->errorMsg = '参数错误';
            return false;
        }
        try {
            if ($this->open($zipFile, self::CREATE) === TRUE) {
                if ($this->addEmptyDir($newDir)) {
                    return true;
                } else {
                    $this->errorMsg = '添加失败';
                    return false;
                }
                $this->close();
            } else {
                $this->errorMsg = '添加失败';
                return false;
            }
        } catch (\Exception $e) {
            $this->errorMsg = $e->getMessage();
            return false;
        }

    }

    /**
     * 压缩目录下添加文件
     * @author: luojianglai
     * @Date:   2018-10-10
     */
    public function addZipFile($zipFile, $addFile, $rename = '')
    {

        if (empty($zipFile) || !is_file($addFile)) {
            $this->errorMsg = '参数错误';
            return false;
        }
        try {
            if (empty($rename)) {
                $rename = basename($addFile);
            }

            if ($this->open($zipFile, self::CREATE) === TRUE) {
                $this->addFile($addFile, $rename);
                $this->close();
                return true;
            } else {
                $this->errorMsg = '创建失败';
                return false;
            }
        } catch (\Exception $e) {
            $this->errorMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * 指定内容到压缩文件
     * @author: luojianglai
     * @Date:   2018-10-10
     */
    public function addString($zipFile, $fileName, $content)
    {
        if (empty($zipFile) || empty($fileName) || empty($content)) {
            $this->errorMsg = '参数错误';
            return false;
        }

        try {
            if ($this->open($zipFile, self::CREATE) === TRUE) {
                $this->addFromString($fileName, $content);
                $this->close();
                return true;
            } else {
                $this->errorMsg = '添加失败';
                return false;
            }
        } catch (\Exception $e) {
            $this->errorMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * 遍历目录到压缩文件
     * @author: luojianglai
     * @Date:   2018-10-10
     */
    public function addDir($zipFile, $path)
    {
        try {
            $nodes = glob($path . '/*');
            if ($this->open($zipFile, self::CREATE) === TRUE) {
                foreach ($nodes as $node) {
                    if (is_dir($node)) {
                        $this->addDir($node);
                    } else if (is_file($node)) {
                        $this->addFile($node);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->errorMsg = $e->getMessage();
            return false;
        }

    }


} // end of the 'zipfile' class


?>
