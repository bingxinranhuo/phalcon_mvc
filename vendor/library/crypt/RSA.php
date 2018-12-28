<?php
/**
 *Copyright (C) qiaodata.com 2018 All rights reserved
 * @author luojianglai
 * @date   2018/9/28 14:54
 */

namespace  Qd\Utils\Crypt;

class RSA
{
    public static $privateKey = '-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQCr4cA76lRdE3KESOlxp51XTqMOD5Im3FWf3Sj/hiJ/oYZ1A3cH
Sm+qEnZAOP3sskNMnJBwG4+UwN+xsm9j74UtekHzg0rJhRa2Ui6VgeQQIuZMdoBZ
hs94MA/B9mTsrw5rnMGg/Z1TAQENO27G50VgtFiKLtQk0UF3V4abbolDZQIDAQAB
AoGAfRc/IFvaKiMNJNkrjFvAVtoUMReD9mRErKP4Qn8MfHbBtEdhSR0TR2NITFUt
+CWEdS08sVpdlJUKN/j0uHaJJO19zkB1mjub3k6dXddWdtQcJavYklpiUUVHq2py
NXu5Uf9nhsgFf35b5JVl2tN2VmOCC9kF1QIlWZMFDEPrbcECQQDkILJGcnsK2wMA
v0vfZYnEWXKXNAToeIdl8CwOUSUsBOXpIfCW6rffKa12qQWyuSPQGxANMiEKqTTt
jbaidzxnAkEAwOHQfX1kHK4fH6YupOAtWAHvYnEueSE0a4ZKfrf6GsnmREaZ51ya
QLIsaDoIAQPl2FgEP5hSOYddVaBxrG0iUwJAbE4BlWSAefTkhhRL5zGDqI520sZF
lkCyt3jA0cNAjJd+t6H/YQbqaK6WxgEUzXcMQ561ji5AdZlDoYxoKtXE/QJBAIMe
/6TPgSceF3pkdoEPBMQ3TI0XdAhUUlZmTG4ok5Vye18ev7FQemxQs2+HQ7ms9KtF
6l1xJzSEmSaEk8IrzpECQQCDINKIgWo62Xiu+VfOhnEconhSJY61lkWppXVt+4u8
ryLPx+wZ/mtg1wKqRNwipzH4VInI96jRK5v5mjJf8nny
-----END RSA PRIVATE KEY-----';


    public static $publicKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCr4cA76lRdE3KESOlxp51XTqMO
D5Im3FWf3Sj/hiJ/oYZ1A3cHSm+qEnZAOP3sskNMnJBwG4+UwN+xsm9j74UtekHz
g0rJhRa2Ui6VgeQQIuZMdoBZhs94MA/B9mTsrw5rnMGg/Z1TAQENO27G50VgtFiK
LtQk0UF3V4abbolDZQIDAQAB
-----END PUBLIC KEY-----';

    public static $encryptMaxLen = 117; //解密一次最大长度
    public static $decryptMaxLen = 128; //加密一次最大长度

    public function __construct($privateKey = '', $publicKey = '')
    {
        if(!empty($privateKey)){
            self::$privateKey = $privateKey;
        }
        if(!empty($publicKey)){
            self::$publicKey = $publicKey;
        }
    }

    /**
     * 获取私钥
     * @return bool|resource
     * @author luojianglai@qiaodata.com
     * @date   2018/9/28 14:56
     */
    private static function getPrivateKey()
    {
        $privateKey = self::$privateKey;
        return openssl_pkey_get_private($privateKey);
    }

    /**
     * 获取公钥
     * @return resource
     * @author luojianglai@qiaodata.com
     * @date   2018/9/28 16:11
     */
    private static function getPublicKey()
    {
        $publicKey = self::$publicKey;
        return openssl_pkey_get_public($publicKey);
    }

    /**
     * 公钥加密
     * @param string $data
     * @return null|string
     * @author luojianglai@qiaodata.com
     * @date   2018/9/28 14:58
     */
    public static function publicEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        $encryptedRe = '';
        foreach (str_split($data, self::$encryptMaxLen) as $chunk) {
            openssl_public_encrypt($chunk, $encrypted, self::getPublicKey());
            $encryptedRe .= $encrypted;
        }
        return base64_encode($encryptedRe);
    }


    /**
     * 私钥解密
     * @param string $encrypted
     * @return null
     * @author luojianglai@qiaodata.com
     * @date   2018/9/28 14:58
     */
    public static function privateDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }

        $encryptedRe = '';
        foreach (str_split(base64_decode($encrypted), self::$decryptMaxLen) as $chunk) {
            openssl_private_decrypt($chunk, $decrypted, self::getPrivateKey());
            $encryptedRe .= $decrypted;
        }
        return $encryptedRe;
    }


    /**
     * 私钥加密
     * @param string $data
     * @return null|string
     * @author luojianglai@qiaodata.com
     * @date   2018/9/28 14:56
     */
    public static function privateEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        $encryptedRe = '';
        foreach (str_split($data, self::$encryptMaxLen) as $chunk) {
            openssl_private_encrypt($chunk, $encrypted, self::getPrivateKey());
            $encryptedRe .= $encrypted;
        }
        return base64_encode($encryptedRe);
    }


    /**
     * 公钥解密
     * @param string $encrypted
     * @return null
     * @author luojianglai@qiaodata.com
     * @date   2018/9/28 14:59
     */
    public static function publicDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        $encryptedRe = '';
        foreach (str_split(base64_decode($encrypted), self::$decryptMaxLen) as $chunk) {
            openssl_public_decrypt($chunk, $decrypted, self::getPublicKey());
            $encryptedRe .= $decrypted;
        }
        return $encryptedRe;
    }
}
