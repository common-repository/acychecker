<?php


namespace AcyChecker\Services;


class UrlService
{
    public static function mainURL(&$link)
    {
        static $mainUrl = '';
        static $otherArguments = false;
        if (empty($mainUrl)) {
            $urls = parse_url(ACYC_LIVE);
            if (isset($urls['path']) && strlen($urls['path']) > 0) {
                $mainUrl = substr(ACYC_LIVE, 0, strrpos(ACYC_LIVE, $urls['path'])).'/';
                $otherArguments = trim(str_replace($mainUrl, '', ACYC_LIVE), '/');
                if (strlen($otherArguments) > 0) {
                    $otherArguments .= '/';
                }
            } else {
                $mainUrl = ACYC_LIVE;
            }
        }

        if ($otherArguments && strpos($link, $otherArguments) === false) {
            $link = $otherArguments.$link;
        }

        return $mainUrl;
    }
}
