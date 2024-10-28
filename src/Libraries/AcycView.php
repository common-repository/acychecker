<?php


namespace AcyChecker\Libraries;


use AcyChecker\Services\DebugService;
use AcyCheckerCmsServices\Extension;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Message;
use AcyCheckerCmsServices\Miscellaneous;
use AcyCheckerCmsServices\Router;
use AcyCheckerCmsServices\Security;
use AcyCheckerCmsServices\Url;

class AcycView extends AcycObject
{
    private $controllerName;
    private $view;
    private $data;

    public function __construct($controllerName, $view, $data)
    {
        parent::__construct();

        $this->controllerName = $controllerName;
        $this->view = $view;
        $this->data = $data;
        $this->includeStyles();
        $this->includeScripts();

        $this->wrapperStart();
        $this->includeMenu();
        $this->includeHeader();
        $this->containerStart();
        $this->includeView();
        $this->containerEnd();
        $this->menuEnd();
        $this->wrapperEnd();
    }

    private function includeStyles()
    {
        Router::addStyle(false, ACYC_CSS.'style.min.css?time='.time());

        // Add the controller style if exists
        if (file_exists(ACYC_MEDIA.'css'.DS.'controllers'.DS.strtolower($this->controllerName).'.min.css')) {
            Router::addStyle(false, ACYC_CSS.'controllers/'.strtolower($this->controllerName).'.min.css?time='.time());
        }

        // Add the layout style if exists
        if (file_exists(ACYC_MEDIA.'css'.DS.'layouts'.DS.strtolower($this->controllerName).DS.strtolower($this->view).'.min.css')) {
            Router::addStyle(false, ACYC_CSS.'layouts/'.strtolower($this->controllerName).'/'.strtolower($this->view).'.min.css?time='.time());
        }
    }

    private function includeScripts()
    {
        $this->includeLanguagesJavascript();
        $this->loadAssets();
        Router::initView();
        Router::addScript(false, ACYC_JS.'index.min.js?time='.time());
        Router::addScript(false, ACYC_JS.'services/pagination.min.js?time='.time());
        Router::addScript(false, ACYC_JS.'services/status.min.js?time='.time());
        Router::addScript(false, ACYC_JS.'services/tooltip.min.js?time='.time());
        Router::addScript(false, ACYC_JS.'services/notice.min.js?time='.time());
        Router::addScript(false, ACYC_JS.'services/database.min.js?time='.time());
        Router::addScript(false, ACYC_JS.'services/modal.min.js?time='.time());
        Router::addScript(false, ACYC_JS.'services/form.min.js?time='.time());
        Router::addScript(false, ACYC_JS.'services/fields.min.js?time='.time());
        Router::addScript(false, ACYC_JS.'services/listing.min.js?time='.time());
        Router::addScript(false, ACYC_JS.'services/ajax.min.js?time='.time());
        if (Miscellaneous::isLeftMenuNecessary()) {
            Router::addScript(false, ACYC_JS.'services/cookie.min.js?time='.time());
            Router::addScript(false, ACYC_JS.'services/joomlaMenu.min.js?time='.time());
        }

        if (strtolower($this->controllerName) === 'dashboard') {
            Router::addScript(false, ACYC_JS.'libraries/apexchart.min.js');
        }

        Router::addScript(false, ACYC_JS.'libraries/select2.min.js');
        Router::addStyle(false, ACYC_CSS.'libraries/select2.min.css');

        // Add the controller script if exists
        if (file_exists(ACYC_MEDIA.'js'.DS.'controllers'.DS.strtolower($this->controllerName).'.min.js')) {
            Router::addScript(false, ACYC_JS.'controllers/'.strtolower($this->controllerName).'.min.js?time='.time());
        }

        // Add the layout script if exists
        if (file_exists(ACYC_MEDIA.'js'.DS.'layouts'.DS.strtolower($this->controllerName).DS.strtolower($this->view).'.min.js')) {
            Router::addScript(false, ACYC_JS.'layouts/'.strtolower($this->controllerName).'/'.strtolower($this->view).'.min.js?time='.time());
        }
    }

    private function includeMenu()
    {
        if (Miscellaneous::isLeftMenuNecessary()) echo Miscellaneous::getLeftMenu(strtolower($this->controllerName)).'<div id="acyc_content">';
    }

    private function menuEnd()
    {
        if (Miscellaneous::isLeftMenuNecessary()) echo '</div>';
    }

    private function includeView()
    {
        $filePath = ACYC_CORE_VIEW.$this->controllerName.DS.$this->view.'.php';
        if (!file_exists($filePath)) Router::redirect(Url::completeLink('dashboard', false, true));

        include_once $filePath;
    }

    private function wrapperStart()
    {
        echo '<div id="acyc_wrapper">';
    }

    private function wrapperEnd()
    {
        echo '</div>';
    }

    private function includeHeader()
    {
        if (!empty($this->data['header'])) echo $this->data['header'];
        Message::displayMessages();
    }

    private function containerStart()
    {
        echo '<div class="cell grid-x">';
    }

    private function containerEnd()
    {
        echo '</div>';
    }

    private function includeLanguagesJavascript()
    {
        $languages = [
            'ACYC_SAVE' => __('Save', 'acychecker'),
            'ACYC_PROCESS' => __('Process', 'acychecker'),
            'ACYC_DISPOSABLE' => __('Disposable', 'acychecker'),
            'ACYC_FREE' => __('Free', 'acychecker'),
            'ACYC_ACCEPT_ALL' => __('Accept all', 'acychecker'),
            'ACYC_ROLE_EMAIL' => __('Role email', 'acychecker'),
            'ACYC_ROLE_BASED' => __('Role based', 'acychecker'),
            'ACYC_FREE_DOMAIN' => __('Free domain', 'acychecker'),
            'ACYC_INVALID_SMTP' => __('Invalid SMTP', 'acychecker'),
            'ACYC_DOMAIN_NOT_EXISTS' => __('Domain doesn\'t exist', 'acychecker'),
            'ACYC_TOTAL' => __('Total', 'acychecker'),
            'ACYC_ARE_YOUR_SURE_FREE_DOMAINS_CONFIRM' => __('Are you sure to block people using free domains like Gmail or Yahoo from your website?', 'acychecker'),
            'ACYC_CONFIRM_DELETE_ALL_TESTS' => __('Please confirm you would like to delete all the previous test results', 'acychecker'),
            'ACYC_NO_RESULTS_FOUND' => __('No results found', 'acychecker'),
            'ACYC_SELECT2_SEARCHING' => __('Searching...', 'acychecker'),
            'ACYC_SELECT2_LIMIT_X_ITEMS' => __('You can only select %s items', 'acychecker'),
            'ACYC_SELECT2_LOADING_MORE_RESULTS' => __('Loading more results...', 'acychecker'),
            'ACYC_SELECT2_ENTER_X_CHARACTERS' => __('Please enter %s or more characters', 'acychecker'),
            'ACYC_SELECT2_DELETE_X_CHARACTERS' => __('Please delete %s characters', 'acychecker'),
            'ACYC_SELECT2_RESULTS_NOT_LOADED' => __('The results could not be loaded.', 'acychecker'),
            'ACYC_CONFIRM_CANCEL_TESTS' => __('Please confirm you would like to cancel pending tests', 'acychecker'),
            'ACYC_BLACKLISTED' => __('Blacklisted', 'acychecker'),
            'ACYC_ARE_YOU_SURE' => __('Are you sure?', 'acychecker'),
            'ACYC_UNBLOCK_USERS_CONFIRM' => __('Are you sure you want to unblock these users?', 'acychecker'),
            'ACYC_BLOCK_USERS_CONFIRM' => __('Are you sure you want to block these users?', 'acychecker'),
            'ACYC_DELETE_USERS_CONFIRM' => __('Are you sure you want to delete these users? This cannot be cancelled afterwards.', 'acychecker'),
            'ACYC_ARE_YOUR_SURE_DELETE_USERS_CONFIRM' => __('Be careful you will really delete the users, are you sure of that?', 'acychecker'),
            'ACYC_MANUAL' => __('Manual action', 'acychecker'),
            'ACYC_PLEASE_SELECT_A_CONDITION' => __('Please select at least one condition first', 'acychecker'),
            'ACYC_ARE_YOU_SURE_DELETE' => __('Are you sure you want to delete these elements?', 'acychecker'),
            'ACYC_USERS_BLOCKED' => __('%s users blocked', 'acychecker'),
            'ACYC_USERS_DELETED' => __('%s users deleted', 'acychecker'),
            'ACYC_SELECT_FINISHED_TESTS' => __('Please select only finished tests', 'acychecker'),
            'ACYC_NO_USER_TABLE_SELECTED' => __('Please select at least one table', 'acychecker'),
            'ACYC_LAST_CONFIRMATION' => __('There is no confirmation past this point, are you sure about the selected conditions?', 'acychecker'),
            'ACYC_ACTIONS_EXECUTED_X_MATCHING' => __('Actions will be executed on the %s users matching the selected conditions.', 'acychecker'),
            'ACYC_ACTIONS_EXECUTED_X_MATCHING_AMONG_SELECTED' => __('Actions will be executed on the %1$s users matching the selected conditions, only among the %2$s you selected.', 'acychecker'),
        ];

        $javascript = 'var ACYC_LANGUAGES = {';

        foreach ($languages as $key => $translation) {
            $javascript .= $key.': "'.Security::escape($translation).'",';
        }
        $javascript = rtrim($javascript, ',');

        $javascript .= '};';

        Router::addScript(true, $javascript);
    }

    private function loadAssets()
    {
        $javascript = '
        var ACYC_CMS = "'.ACYC_CMS.'";';

        if ('joomla' == ACYC_CMS) {
            $javascript .= '
        var ACYC_J40 = "'.ACYC_J40.'";';
        }

        Router::addScript(true, $javascript);
        Router::addScript(
            false,
            ACYC_JS.'libraries/foundation.min.js?v='.filemtime(ACYC_MEDIA.'js'.DS.'libraries'.DS.'foundation.min.js')
        );
    }
}
