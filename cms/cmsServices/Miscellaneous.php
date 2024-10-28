<?php

namespace AcyCheckerCmsServices;


class Miscellaneous
{
    public static function isLeftMenuNecessary()
    {
        // Already one in WP
        return false;
    }

    public static function getLeftMenu($name)
    {
        return '';
    }

    public static function isPluginActive($plugin, $family = 'system')
    {
        return true;
    }

    public static function menuOnly($link)
    {
    }

    public static function disableCmsEditor()
    {
        add_filter(
            'user_can_richedit',
            function ($a) {
                return false;
            },
            50
        );
    }

    public static function isElementorEdition()
    {
        global $post;

        if (empty($post) || !class_exists('\\Elementor\\Plugin')) return false;

        return \Elementor\Plugin::$instance->db->is_built_with_elementor($post->ID);
    }

    public static function session()
    {
        $sessionID = session_id();
        if (empty($sessionID)) {
            @session_start();
        }
    }
}
