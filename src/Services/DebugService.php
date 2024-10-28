<?php


namespace AcyChecker\Services;


use AcyCheckerCmsServices\File;

class DebugService
{
    public static function dump($arg, $ajax = false, $indent = true)
    {
        ob_start();
        var_dump($arg);
        $result = ob_get_clean();

        if ($ajax) {
            file_put_contents(ACYC_BACK.'acyc_debug.txt', $result, FILE_APPEND);
        } else {
            $style = $indent ? 'margin-left: 220px;' : '';
            echo '<pre style="'.$style.'">'.htmlentities($result).'</pre>';
        }
    }

    public static function getLogPath($filename, $create = false)
    {
        $reportPath = ACYC_LOGS_FOLDER.$filename;
        $reportPath = File::cleanPath(ACYC_ROOT.trim(html_entity_decode($reportPath)));
        if ($create) FileService::createDir(dirname($reportPath), true, true);

        return $reportPath;
    }

    public static function logMessage($file, $message)
    {
        $reportPath = self::getLogPath($file, true);

        $lr = "\r\n";
        file_put_contents(
            $reportPath,
            $lr.DateService::getDate(time()).' - '.$message,
            FILE_APPEND
        );
    }
}
