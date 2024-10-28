<?php

namespace AcyCheckerCmsServices;


class Extension
{
    public static function isExtensionActive($extension)
    {
        if (function_exists('is_plugin_active')) return is_plugin_active($extension);

        return file_exists(WP_PLUGIN_DIR.DS.$extension);
    }

    public static function getPluginsPath($file, $dir)
    {
        return substr(plugin_dir_path($file), 0, strpos(plugin_dir_path($file), plugin_basename($dir)));
    }
}
