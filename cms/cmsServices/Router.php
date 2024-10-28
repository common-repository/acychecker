<?php

namespace AcyCheckerCmsServices;


class Router
{
    public static function initView()
    {
        self::addScript(
            true,
            '
            var ACYC_AJAX_URL = "'.admin_url('admin-ajax.php').'?action='.ACYC_COMPONENT.'_router&'.Form::noTemplate().'&'.Form::getFormToken().'&nocache='.time().'";
            var ACYC_IS_ADMIN = '.(Security::isAdmin() ? 'true' : 'false').';'
        );
    }

    public static function addScript($raw, $script, $type = 'text/javascript', $defer = false, $async = false, $needTagScript = false, $deps = ['jquery'])
    {
        static $scriptNumber = 0;
        $scriptNumber++;
        if ($raw) {
            wp_register_script('acyc_script'.$scriptNumber, '', $deps);
            wp_enqueue_script('acyc_script'.$scriptNumber);
            wp_add_inline_script('acyc_script'.$scriptNumber, $script);
        } else {
            wp_enqueue_script('acyc_script'.$scriptNumber, $script, $deps);
        }

        return 'acyc_script'.$scriptNumber;
    }

    public static function addStyle($raw, $style, $type = 'text/css', $media = null, $attribs = [])
    {
        static $styleNumber = 0;
        $styleNumber++;
        if ($raw) {
            wp_add_inline_style('style'.$styleNumber, $style);
        } else {
            wp_enqueue_style('style'.$styleNumber, $style);
        }
    }

    public static function redirect($url, $msg = '', $msgType = 'message', $safe = false)
    {
        if (Security::isAdmin() && substr($url, 0, 4) != 'http' && substr($url, 0, 4) != 'www.') {
            $url = Url::addPageParam($url);
        }
        if (empty($url)) $url = Url::rootURI();
        if (headers_sent()) {
            echo '<script type="text/javascript">window.location.href = "'.addslashes($url).'";</script>';
        } else {
            if ($safe) {
                wp_safe_redirect($url);
            } else {
                wp_redirect($url);
            }
        }
        exit;
    }
}
