<?php

namespace AcyChecker\Services;


use AcyCheckerCmsServices\File;
use AcyCheckerCmsServices\Message;

class FileService
{
    public static function createDir($dir, $report = true, $secured = false)
    {
        if (is_dir($dir)) return true;

        $indexhtml = '<html><body bgcolor="#FFFFFF"></body></html>';

        //Create the directory with an index file inside
        try {
            $status = File::createFolder($dir);
        } catch (\Exception $e) {
            $status = false;
        }

        if (!$status) {
            if ($report) {
                Message::display('Could not create the directory '.$dir, 'error');
            }

            return false;
        }

        try {
            $status = File::writeFile($dir.DS.'index.html', $indexhtml);
        } catch (\Exception $e) {
            $status = false;
        }

        if (!$status) {
            if ($report) {
                Message::display('Could not create the file '.$dir.DS.'index.html', 'error');
            }
        }

        if ($secured) {
            try {
                $htaccess = 'Order deny,allow'."\r\n".'Deny from all';
                $status = File::writeFile($dir.DS.'.htaccess', $htaccess);
            } catch (\Exception $e) {
                $status = false;
            }

            if (!$status) {
                if ($report) {
                    Message::display('Could not create the file '.$dir.DS.'.htaccess', 'error');
                }
            }
        }

        return $status;
    }

    public static function bytes($val)
    {
        $val = trim($val);
        if (empty($val)) {
            return 0;
        }

        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            case 'g':
                $val = intval($val) * 1073741824;
                break;
            case 'm':
                $val = intval($val) * 1048576;
                break;
            case 'k':
                $val = intval($val) * 1024;
                break;
        }

        return (int)$val;
    }
}
