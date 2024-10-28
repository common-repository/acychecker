<?php

namespace AcyCheckerCmsServices;


class Security
{
    public static function getVar($type, $name, $default = null, $source = 'REQUEST', $mask = 0)
    {
        $source = strtoupper($source);

        switch ($source) {
            case 'GET':
                $input = &$_GET;
                break;
            case 'POST':
                $input = &$_POST;
                break;
            case 'FILES':
                $input = &$_FILES;
                break;
            case 'COOKIE':
                $input = &$_COOKIE;
                break;
            case 'ENV':
                $input = &$_ENV;
                break;
            case 'SERVER':
                $input = &$_SERVER;
                break;
            default:
                $source = 'REQUEST';
                $input = &$_REQUEST;
                break;
        }

        if (!isset($input[$name])) {
            return $default;
        }

        $result = $input[$name];
        unset($input);
        if ($type == 'array') {
            $result = (array)$result;
        }

        // WP alters every variable in $_REQUEST... Seriously...
        if (in_array($source, ['POST', 'REQUEST', 'GET', 'COOKIE'])) {
            $result = Security::stripslashes($result);
        }

        return Security::cleanVar($result, $type, $mask);
    }

    public static function stripslashes($element)
    {
        if (is_array($element)) {
            foreach ($element as &$oneCell) {
                $oneCell = Security::stripslashes($oneCell);
            }
        } elseif (is_string($element)) {
            $element = stripslashes($element);
        }

        return $element;
    }

    public static function cleanVar($var, $type, $mask)
    {
        if (is_array($var)) {
            foreach ($var as $i => $val) {
                $var[$i] = Security::cleanVar($val, $type, $mask);
            }

            return $var;
        }

        switch ($type) {
            case 'string':
                $var = (string)$var;
                break;
            case 'int':
                $var = (int)$var;
                break;
            case 'float':
                $var = (float)$var;
                break;
            case 'boolean':
                $var = (boolean)$var;
                break;
            case 'word':
                $var = preg_replace('#[^a-zA-Z_]#', '', $var);
                break;
            case 'cmd':
                $var = preg_replace('#[^a-zA-Z0-9_\.-]#', '', $var);
                $var = ltrim($var, '.');
                break;
            default:
                break;
        }

        if (!is_string($var)) {
            return $var;
        }

        $var = trim($var);

        if ($mask & 2) {
            return $var;
        }

        if (!preg_match('//u', $var)) {
            // String contains invalid byte sequence, remove it
            $var = htmlspecialchars_decode(htmlspecialchars($var, ENT_IGNORE, 'UTF-8'));
        }

        if (!($mask & 4)) {
            $var = preg_replace('#<[a-zA-Z/]+[^>]*>#Uis', '', $var);
        }

        return $var;
    }

    public static function setVar($name, $value = null, $hash = 'REQUEST', $overwrite = true)
    {
        $hash = strtoupper($hash);

        switch ($hash) {
            case 'GET':
                $input = &$_GET;
                break;
            case 'POST':
                $input = &$_POST;
                break;
            case 'FILES':
                $input = &$_FILES;
                break;
            case 'COOKIE':
                $input = &$_COOKIE;
                break;
            case 'ENV':
                $input = &$_ENV;
                break;
            case 'SERVER':
                $input = &$_SERVER;
                break;
            default:
                $input = &$_REQUEST;
                break;
        }

        if (!isset($input[$name]) || $overwrite) {
            $input[$name] = $value;
        }
    }

    public static function raiseError($level, $code, $msg, $info = null)
    {
        Message::display($code.': '.$msg, 'error');
        wp_die();
    }

    public static function isAdmin()
    {
        $page = Security::getVar('string', 'page', '');

        if (!empty($page)) {
            return $page !== 'front';
        } else {
            return is_admin();
        }
    }

    public static function cmsLoaded()
    {
        defined('ABSPATH') || die('Restricted access');
    }

    public static function isDebug()
    {
        return defined('WP_DEBUG') && WP_DEBUG;
    }

    public static function askLog($current = true, $message = 'ACYC_NOTALLOWED', $type = 'error')
    {
        //If the user is not logged in, we just redirect him to the login page....
        $url = Url::rootURI().'wp-login.php';
        if ($current) {
            $url .= '&redirect_to='.base64_encode(Url::currentURL());
        }

        Router::redirect($url, $message, $type);
    }

    public static function triggerCmsHook($action, $args = [])
    {
        array_unshift($args, $action);

        return call_user_func_array('do_action', $args);
    }

    public static function arrayToInteger(&$array)
    {
        if (is_array($array)) {
            $array = array_map('intval', $array);
        } else {
            $array = [];
        }
    }

    public static function escape($text)
    {
        if (is_array($text) || is_object($text)) {
            $text = json_encode($text);
        }

        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
