<?php

use AcyCheckerCmsServices\Url;
use AcyCheckerCmsServices\Language;

define('ACYC_ACYMAILLING_WEBSITE', 'https://www.acymailing.com/');
define('ACYC_ACYCHECKER_WEBSITE', 'https://www.acychecker.com/');
define('ACYC_UPDATEME_API_URL', 'https://api.acymailing.com/');
define('ACYC_UPDATEMEURL', 'https://www.acyba.com/index.php?option=com_updateme&nocache='.time().'&ctrl=');
define('ACYC_UPDATEURL', ACYC_UPDATEMEURL.'update&task=');
define('ACYC_LIVE', rtrim(Url::rootURI(), '/').'/');
define('ACYC_LANGUAGE_FILE', 'com_acychecker');
define('ACYC_API_URL', 'https://api.acychecker.com/');
define('ACYC_DOC_URL', 'https://docs.acychecker.com/');

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

include_once __DIR__.DIRECTORY_SEPARATOR.'defines_cms.php';

define('ACYC_CORE', ACYC_BACK.'src'.DS);
define('ACYC_CORE_VIEW', ACYC_CORE.'Views'.DS);

Language::loadLanguageFile('com_acychecker');
