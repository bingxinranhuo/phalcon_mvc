<?php
/**
 * 配置入口,自动载入
 */
use Phalcon\Config;

$config = autoload();
return new Config($config);
/**
 * 根据 ENVIRONMENT 自动载入配置
 * @return array
 * @author luojianglai@qiaodata.com
 * @date   2018/12/24 18:34
 */
function autoload()
{
    $conf = [];
    $dir = CONF_PATH . ENVIRONMENT . '/';
    if (is_dir($dir) && $dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (filetype($dir . $file) == 'file') {
                $fileInfo = pathinfo($file);
                if ($fileInfo['extension'] == 'php') {
                    $tmp = include $dir . $file;
                    $conf = array_merge($conf, $tmp);
                }
            }
        }
        closedir($dh);
    }
    return $conf;
}

