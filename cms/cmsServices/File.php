<?php

namespace AcyCheckerCmsServices;


class File
{
    public static function fileGetContent($url, $timeout = 10)
    {
        if (strpos($url, '_custom.ini') !== false && !file_exists($url)) {
            return '';
        }

        ob_start();
        $data = '';

        if (strpos($url, 'http') === 0 && class_exists('WP_Http') && method_exists('WP_Http', 'request')) {
            $args = ['timeout' => $timeout];
            $request = new WP_Http();
            $data = $request->request($url, $args);
            $data = (empty($data) || !is_array($data) || empty($data['body'])) ? '' : $data['body'];
        }

        if (empty($data) && function_exists('file_get_contents')) {
            if (!empty($timeout)) {
                ini_set('default_socket_timeout', $timeout);
            }
            $streamContext = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
            $data = file_get_contents($url, false, $streamContext);
        }

        if (empty($data) && function_exists('fopen') && function_exists('stream_get_contents')) {
            $handle = fopen($url, "r");
            if (!empty($timeout)) {
                stream_set_timeout($handle, $timeout);
            }
            $data = stream_get_contents($handle);
        }
        $warnings = ob_get_clean();

        if (Security::isDebug()) {
            echo esc_html($warnings);
        }

        return $data;
    }

    public static function getFolders($path, $filter = '.', $recurse = false, $full = false, $exclude = ['.svn', 'CVS', '.DS_Store', '__MACOSX'], $excludefilter = ['^\..*'])
    {
        $path = self::cleanPath($path);

        if (!is_dir($path)) {
            Message::enqueueMessage(sprintf(__('%s is not a folder', 'acychecker'), $path), 'error');

            return [];
        }

        if (count($excludefilter)) {
            $excludefilter_string = '/('.implode('|', $excludefilter).')/';
        } else {
            $excludefilter_string = '';
        }

        $arr = self::getItems($path, $filter, $recurse, $full, $exclude, $excludefilter_string, false);
        asort($arr);

        return array_values($arr);
    }

    public static function getItems($path, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles)
    {
        $arr = [];

        if (!($handle = @opendir($path))) {
            return $arr;
        }

        while (($file = readdir($handle)) !== false) {
            if ($file == '.' || $file == '..' || in_array($file, $exclude) || (!empty($excludefilter_string) && preg_match(
                        $excludefilter_string,
                        $file
                    ))) {
                continue;
            }
            $fullpath = $path.'/'.$file;

            $isDir = is_dir($fullpath);

            if (($isDir xor $findfiles) && preg_match("/$filter/", $file)) {
                if ($full) {
                    $arr[] = $fullpath;
                } else {
                    $arr[] = $file;
                }
            }

            if ($isDir && $recurse) {
                if (is_int($recurse)) {
                    $arr = array_merge(
                        $arr,
                        self::getItems(
                            $fullpath,
                            $filter,
                            $recurse - 1,
                            $full,
                            $exclude,
                            $excludefilter_string,
                            $findfiles
                        )
                    );
                } else {
                    $arr = array_merge(
                        $arr,
                        self::getItems(
                            $fullpath,
                            $filter,
                            $recurse,
                            $full,
                            $exclude,
                            $excludefilter_string,
                            $findfiles
                        )
                    );
                }
            }
        }

        closedir($handle);

        return $arr;
    }

    public static function cleanPath($path, $ds = DIRECTORY_SEPARATOR)
    {
        $path = trim($path);

        if (empty($path)) {
            $path = ACYC_ROOT;
        } elseif (($ds == '\\') && substr($path, 0, 2) == '\\\\') {
            $path = "\\".preg_replace('#[/\\\\]+#', $ds, $path);
        } else {
            $path = preg_replace('#[/\\\\]+#', $ds, $path);
        }

        return $path;
    }

    public static function getFiles(
        $path,
        $filter = '.',
        $recurse = false,
        $full = false,
        $exclude = ['.svn', 'CVS', '.DS_Store', '__MACOSX'],
        $excludefilter = [
            '^\..*',
            '.*~',
        ],
        $naturalSort = false
    ) {
        $path = self::cleanPath($path);

        if (!is_dir($path)) {
            Message::enqueueMessage(sprintf(__('%s is not a folder', 'acychecker'), $path), 'error');

            return false;
        }

        if (count($excludefilter)) {
            $excludefilter_string = '/('.implode('|', $excludefilter).')/';
        } else {
            $excludefilter_string = '';
        }

        $arr = self::getItems($path, $filter, $recurse, $full, $exclude, $excludefilter_string, true);

        if ($naturalSort) {
            natsort($arr);
        } else {
            asort($arr);
        }

        return array_values($arr);
    }

    public static function writeFile($file, $buffer, $flags = 0)
    {
        if (!file_exists(dirname($file)) && self::createFolder(dirname($file)) == false) {
            return false;
        }
        $file = self::cleanPath($file);

        return is_int(file_put_contents($file, $buffer, $flags));
    }

    public static function createFolder($path = '', $mode = 0755)
    {
        $path = self::cleanPath($path);
        if (file_exists($path)) {
            return true;
        }

        $origmask = @umask(0);
        $ret = @mkdir($path, $mode, true);
        @umask($origmask);

        return $ret;
    }
}
