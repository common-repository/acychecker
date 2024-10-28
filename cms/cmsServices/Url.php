<?php

namespace AcyCheckerCmsServices;


class Url
{
    public static function route($url, $xhtml = true, $ssl = null)
    {
        return Url::baseURI().$url;
    }

    public static function addPageParam($url, $ajax = false, $front = false)
    {
        preg_match('#^([a-z]+)(?:[^a-z]|$)#Uis', $url, $ctrl);

        if ($front) {
            $link = 'index.php?page=acychecker_front&ctrl='.$url;
            if ($ajax) $link .= '&'.Form::noTemplate();
        } else {
            $link = 'admin.php?page=acychecker_'.$ctrl[1].'&ctrl='.$url;
            if ($ajax) {
                $link .= '&action=acychecker_router&'.Form::noTemplate();
            }
        }

        return $link;
    }

    public static function baseURI($pathonly = false)
    {
        if (Security::isAdmin()) {
            return admin_url();
        }

        return Url::rootURI();
    }

    public static function completeLink($link, $popup = false, $redirect = false, $forceNoPopup = false)
    {
        if (($popup || Form::isNoTemplate()) && $forceNoPopup === false) {
            $link .= '&'.Form::noTemplate();
        }

        $link = Url::addPageParam($link);

        return Url::route($link);
    }

    /**
     * If you use it to prepare a POST ajax, make sure you add the action and page parameters to the data passed, it's not taken into account if it's only in the URL
     */
    public static function prepareAjaxURL($url)
    {
        return htmlspecialchars_decode(Url::route(Url::addPageParam($url, true)));
    }

    public static function getMenu()
    {
        return get_post();
    }

    public static function rootURI($pathonly = false, $path = 'siteurl')
    {
        return get_option($path).'/';
    }

    public static function currentURL()
    {
        $url = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $url .= '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        return $url;
    }

    public static function frontendLink($link, $complete = true)
    {
        return Url::rootURI().Url::addPageParam($link, true, true);
    }
}
