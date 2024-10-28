<?php
/*
Plugin Name: AcyChecker
Description: Check the email addresses of your site and block them if they do not exists
Author: Acyba
Author URI: https://www.acychecker.com
License: GPLv3
Version: 1.4.0
Text Domain: acychecker
*/

include_once __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
include_once __DIR__.DIRECTORY_SEPARATOR.'defines.php';

new \AcyCheckerCmsServices\WordPressMenu();
new \AcyCheckerCmsServices\WordPressActivation();
new \AcyCheckerCmsServices\WordPressRegistration();
new \AcyCheckerCmsServices\WordPressCron();
new \AcyCheckerCmsServices\WordPressBlockUser();
