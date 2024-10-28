<?php

define('ACYC_CMS', 'wordpress');
define('ACYC_CMS_TITLE', 'WordPress');
define('ACYC_COMPONENT', 'acychecker');
define('ACYC_ACYMAILING_COMPONENT', 'acymailing');
define('ACYC_ACYMAILING5_COMPONENT', 'acymailing5');
define('ACYC_DEFAULT_LANGUAGE', 'en-US');
define('ACYC_ADMIN_GROUP', 'administrator');

define('ACYC_BASE', '');
// On wordpress.com, websites have access restrictions on the base folder. According to them we need $_SERVER['DOCUMENT_ROOT'] instead of the standard ABSPATH
$acycAbsPath = ABSPATH;
if (!empty($_SERVER['DOCUMENT_ROOT'])) {
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $pos = strpos(ABSPATH, $docRoot);
    if ($pos !== false) {
        $docRoot .= substr(ABSPATH, $pos + strlen($docRoot));
    }
    $docRoot = rtrim($docRoot, DS.'/').DS;
    if (str_replace($docRoot, '', WP_PLUGIN_DIR.DS) === 'wp-content/plugins/') {
        $acycAbsPath = $docRoot;
    }
}
define('ACYC_FOLDER', WP_PLUGIN_DIR.DS.ACYC_COMPONENT.DS);
define('ACYC_ROOT', rtrim($acycAbsPath, DS.'/').DS);
define('ACYC_BACK', ACYC_FOLDER);
define('ACYC_VIEW', ACYC_BACK.'src'.DS.'Views'.DS);
define('ACYC_MEDIA', ACYC_FOLDER.'assets'.DS);
define('ACYC_WP_UPLOADS', basename(WP_CONTENT_DIR).DS.'uploads'.DS.ACYC_COMPONENT.DS);
define('ACYC_UPLOADS_PATH', ACYC_ROOT.ACYC_WP_UPLOADS);
define('ACYC_LANGUAGE', WP_CONTENT_DIR.'languages'.DS.'plugins'.DS);
define('ACYC_VAR', ACYC_BACK.'var'.DS);

define('ACYC_MEDIA_RELATIVE', str_replace(ACYC_ROOT, '', ACYC_MEDIA));
define('ACYC_PLUGINS_URL', plugins_url());
define('ACYC_MEDIA_URL', ACYC_PLUGINS_URL.'/'.ACYC_COMPONENT.'/assets/');
define('ACYC_IMAGES', ACYC_MEDIA_URL.'images/');
define('ACYC_CSS', ACYC_MEDIA_URL.'css/');
define('ACYC_JS', ACYC_MEDIA_URL.'js/');

define('ACYC_LOGS_FOLDER', ACYC_WP_UPLOADS.'logs'.DS);

define('ACYC_CMSV', get_bloginfo('version'));

define('ACYC_UPLOADS_URL', WP_CONTENT_URL.'/uploads/'.ACYC_COMPONENT.'/');
define('ACYC_MEDIA_FOLDER', str_replace([ABSPATH, ACYC_ROOT], '', WP_PLUGIN_DIR).'/'.ACYC_COMPONENT.'/assets');
