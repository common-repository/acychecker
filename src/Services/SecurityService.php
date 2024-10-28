<?php


namespace AcyChecker\Services;


class SecurityService
{
    public static function arrayToInteger(&$array)
    {
        if (is_array($array)) {
            $array = array_map('intval', $array);
        } else {
            $array = [];
        }
    }

    public static function noCache()
    {
        HttpService::header('Cache-Control: no-store, no-cache, must-revalidate');
        HttpService::header('Cache-Control: post-check=0, pre-check=0', false);
        HttpService::header('Pragma: no-cache');
        HttpService::header('Expires: Wed, 17 Sep 1975 21:32:10 GMT');
    }
}
