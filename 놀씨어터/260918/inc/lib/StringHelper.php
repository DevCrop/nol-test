<?php

class StringHelper {
    // escape string with htmlspecialchars
    public static function escape($str){
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // decode htmlspecialchars back to original
    public static function unescape($str){
        return htmlspecialchars_decode($str, ENT_QUOTES);
    }

    // add single quotes around a string
    public static function quoting($str){
        return '\'' . $str . '\'';
    }

    // add slashes to escape special characters
    public static function addSlash($str){
        return addslashes($str);
    }

    // remove slashes from a string
    public static function removeSlash($str){
        return stripslashes($str);
    }

    // sanitize input by escaping HTML and database special characters
    public static function sanitize($str) {
        if (is_numeric($str)) {
            return $str;
        }

        $db = DB::getInstance(); // PDO 인스턴스 가져오기
        $str = trim($str);
        $str = self::addSlash($str);
        $str = self::escape($str);
        $str = $db->quote($str); // PDO::quote 사용하여 문자열 이스케이프
        return $str;
    }

    // reverse the sanitize process
    public static function unsanitize($str){
        $str = trim($str, '\'');
        $str = self::unescape($str);
        $str = self::removeSlash($str);
        return $str;
    }
}
