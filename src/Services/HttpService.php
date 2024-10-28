<?php

namespace AcyChecker\Services;

class HttpService
{
    public static function header($header, $replace = true)
    {
        if (headers_sent()) return;
        header($header, $replace);
    }

    public static function setDownloadHeaders($filename, $extension = '.csv')
    {
        HttpService::header('Pragma: public');
        HttpService::header('Expires: 0');
        HttpService::header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        HttpService::header('Content-Type: application/force-download');
        HttpService::header('Content-Type: application/octet-stream');
        HttpService::header('Content-Type: application/download');
        HttpService::header('Content-Disposition: attachment; filename='.$filename.$extension);
        HttpService::header('Content-Transfer-Encoding: binary');
    }
}
