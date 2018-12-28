<?php
namespace Qd\Utils\Crypt;
/***************************************************************************
 *
 * Copyright (c) 2018 SpriteSoft, Inc. All Rights Reserved
 * $Id$
 *
 **************************************************************************/


/**
 * @file nc.php
 * @author sprite(@shenyi_)
 * @date 2018/05/23 09:56:57
 * @version $Revision$
 * @brief
 *
 **/
class NumberCrypt
{
    static $CHAR_MAP = [
        ['0', 'L', '6', 'W', 'N', 'K', 'V', 'F', 's', 'O', 'Q', 'D', 'm', 'H', 'P', 'S', 'u', 'B', 'Y', '1', 'U', 'i', 'n', '4', 'a', 'w', 'C', 'd', 'f', '2', 'T', 'X', 'r', 'I', 'M', '3', 'G', 'p', '5', 'J', 'e', 'b', 'A', 'Z', '9', 'z', 'y', 'c', 'h', 'q', 'g', 'E', 'k', 't', 'o', '8', 'x', 'R', '7', 'v', 'j', 'l'],
        ['c', 'U', 'Z', 'F', 'G', 'W', 'p', 'O', 'g', 'K', '0', '3', 'J', 'E', 'C', 'a', 'i', 'j', 'y', 'v', 'Y', 'h', 'q', '5', '4', '7', 'M', 'n', 'd', '8', 'A', 'e', 'b', 'N', 'z', 'r', 'm', '6', '1', 'x', '9', 'X', 't', 'u', 'I', 's', 'V', 'P', 'D', 'B', 'L', 'S', 'T', '2', 'H', 'w', 'k', 'l', 'f', 'R', 'Q', 'o'],
        ['H', 'P', 'j', 'A', 'f', 'w', 'J', 'R', 'a', 'X', 'U', 'v', 'l', 'c', 'i', 'K', 's', 'D', 'T', 'N', '8', '1', 'z', 'g', '7', 'n', 'G', 'x', '5', 'h', '0', 'V', 'L', '3', 'm', 'e', 'r', 't', 'E', '4', 'M', '2', '9', 'O', 'B', 'k', 'S', 'Y', 'y', 'W', 'd', 'C', 'p', 'o', 'F', 'b', 'q', 'Q', '6', 'u', 'Z', 'I'],
        ['6', '8', 'M', 'A', 'u', 'w', 'z', 'l', 'd', 't', '5', 'Z', '9', 'm', 'V', 'k', 'N', 'J', 's', '1', 'Y', 'x', 'W', 'L', 'D', 'r', '7', 'U', 'n', 'G', 'j', 'R', 'i', 'P', 'S', 'C', 'I', 'O', 'T', 'h', 'F', 'f', '3', 'H', 'X', 'q', '4', 'Q', 'o', 'g', 'B', 'a', 'y', 'p', 'v', 'b', 'e', 'c', 'K', '0', 'E', '2'],
        ['k', '5', 'V', 'm', 'H', 'i', '3', 'x', 'u', 'a', 'l', 'U', 'X', 't', 'Y', 'N', '4', 's', 'G', 'c', 'P', '0', 'S', 'D', 'F', 'j', 'K', '6', 'r', 'A', 'h', '2', 'O', 'W', 'o', 'E', 'e', '7', 'y', '8', 'C', 'B', 'v', 'Z', 'q', 'f', 'z', 'n', 'R', 'J', 'L', 'b', '1', 'Q', 'I', 'M', 'w', 'g', 'd', 'p', 'T', '9']
    ];
    static $POS_MAP = [
        [2, 4, 3, 0, 1],
        [2, 4, 0, 1, 3],
        [2, 1, 0, 4, 3],
        [3, 4, 2, 1, 0],
        [3, 4, 1, 2, 0],
        [4, 3, 1, 2, 0],
        [1, 2, 4, 0, 3],
        [0, 2, 1, 4, 3],
        [0, 1, 3, 4, 2],
        [0, 3, 2, 1, 4],
        [3, 4, 0, 2, 1],
        [3, 2, 0, 4, 1],
        [0, 1, 3, 4, 2],
        [0, 3, 2, 1, 4],
    ];
    static $STR_LEN = 5;
    static $CHAR_COUNT = 62;

    static public function shuffleStr($encStr)
    {
        $sl = strlen($encStr);
        $rs = str_repeat('0', $sl);
        $tc = 0;
        for ($i = 0; $i < $sl; $i++) {
            $tc += ord($encStr[$i]);
        }
        $pm = self::$POS_MAP[$tc % count(self::$POS_MAP)];

        for ($i = 0; $i < $sl; $i++) {
            $rs[$i] = $encStr[$pm[$i]];
        }
        return $rs;
    }

    static public function restoreStr($encStr)
    {
        $sl = strlen($encStr);
        $rs = str_repeat('0', $sl);
        $tc = 0;
        for ($i = 0; $i < $sl; $i++) {
            $tc += ord($encStr[$i]);
        }
        $pm = self::$POS_MAP[$tc % count(self::$POS_MAP)];
        for ($i = 0; $i < $sl; $i++) {
            $rs[$pm[$i]] = $encStr[$i];
        }
        return $rs;
    }

    static public function encrypt($number)
    {
        if ($number <= 0) {
            return '';
        }
        $encStr = '';
        for ($i = 0; $i < self::$STR_LEN; $i++) {
            $idx = $number % self::$CHAR_COUNT;
            $number = $number / self::$CHAR_COUNT;
            $encStr = self::$CHAR_MAP[$i][$idx] . $encStr;
        }
        return self::shuffleStr($encStr);
    }

    static public function decrypt($encStr)
    {
        if (strlen($encStr) != 5) {
            return 0;
        }
        $encStr = self::restoreStr($encStr);
        $number = 0;
        $base = 1;
        for ($i = 0; $i < self::$STR_LEN; $i++) {
            $c = $encStr[self::$STR_LEN - $i - 1];
            $pos = array_search($c, self::$CHAR_MAP[$i]);
            if (false === $pos) {
                return 0;
            }
            $number += $pos * $base;
            $base *= self::$CHAR_COUNT;
        }
        return $number;
    }
}

/*
for ($i = 0; $i < 1000; $i++) {
    $r = NumberCrypt::encrypt($i);
    echo($r . " " . NumberCrypt::decrypt($r) . " \n");
}*/
/* vim: set ts=4 sw=4 sts=4 tw=100 noet: */
?>
