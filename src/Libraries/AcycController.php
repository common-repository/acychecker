<?php

namespace AcyChecker\Libraries;


use AcyChecker\Services\DebugService;
use AcyChecker\Services\HeaderService;
use AcyCheckerCmsServices\Extension;
use AcyCheckerCmsServices\Form;
use AcyCheckerCmsServices\Miscellaneous;
use AcyCheckerCmsServices\Router;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Message;
use AcyCheckerCmsServices\Security;
use AcyCheckerCmsServices\Url;

class AcycController extends AcycObject
{
    public $name = '';
    public $defaultTask = 'defaultTask';
    public $breadcrumb = [];
    public $loadScripts = [];
    public $currentClass = null;
    public $sessionName = '';
    public $taskCalled = '';
    public $layout = '';
    public $isAcymailingInstalled;
    public $isAcymailing5Installed;

    public function __construct()
    {
        parent::__construct();

        $classname = get_class($this);
        $classname = substr($classname, strrpos($classname, '\\') + 1);
        $ctrlpos = strpos($classname, 'Controller');
        $this->name = strtolower(substr($classname, 0, $ctrlpos));

        $currentClassName = 'AcyChecker\\Classes\\'.rtrim(ucfirst($this->name), 's').'Class';
        if (class_exists($currentClassName)) $this->currentClass = new $currentClassName;
        $this->sessionName = 'acyc_filters_'.$this->name;
        $this->taskCalled = Security::getVar('string', 'task', '');

        $this->breadcrumb['AcyChecker'] = Url::completeLink('dashboard');
        Miscellaneous::session();
        if (empty($_SESSION[$this->sessionName])) $_SESSION[$this->sessionName] = [];

        $acymExtension = 'joomla' == ACYC_CMS ? ACYC_ACYMAILING_COMPONENT : ACYC_ACYMAILING_COMPONENT.'/index.php';
        $acy5Extension = 'joomla' == ACYC_CMS ? ACYC_ACYMAILING5_COMPONENT : ACYC_ACYMAILING5_COMPONENT.'/index.php';
        $this->isAcymailingInstalled = Extension::isExtensionActive($acymExtension);
        $this->isAcymailing5Installed = Extension::isExtensionActive($acy5Extension);
    }

    public function call($task)
    {
        // If task doesn't exist, redirect to default task + add message
        if (!method_exists($this, $task)) {
            Message::enqueueMessage(__('This page doesn\'t exist', 'acychecker'), 'warning');
            $task = $this->defaultTask;
            Security::setVar('task', $task);
        }

        // Call the task
        $this->$task();
    }

    public function loadScripts($task)
    {
        if (empty($this->loadScripts)) return;

        $scripts = [];
        if (!empty($this->loadScripts['all'])) {
            $scripts = $this->loadScripts['all'];
        }

        if (!empty($task) && !empty($this->loadScripts[$task])) {
            $scripts = array_merge($scripts, $this->loadScripts[$task]);
        }

        if (empty($scripts)) return;

        if (in_array('datepicker', $scripts)) {
            // Must be loaded in the right order
            Router::addScript(false, ACYC_JS.'libraries/moment.min.js?v='.filemtime(ACYC_MEDIA.'js'.DS.'libraries'.DS.'moment.min.js'));
            Router::addScript(false, ACYC_JS.'libraries/rome.min.js?v='.filemtime(ACYC_MEDIA.'js'.DS.'libraries'.DS.'rome.min.js'));
            Router::addScript(false, ACYC_JS.'libraries/material-datetime-picker.min.js?v='.filemtime(ACYC_MEDIA.'js'.DS.'libraries'.DS.'material-datetime-picker.min.js'));
            Router::addStyle(false, ACYC_CSS.'libraries/material-datetime-picker.min.css?v='.filemtime(ACYC_MEDIA.'css'.DS.'libraries'.DS.'material-datetime-picker.min.css'));
        }
    }

    public function setdefaultTask($task)
    {
        $this->defaultTask = $task;
    }

    public function getName()
    {
        return $this->name;
    }

    public function display($data = [])
    {
        if (!Form::isNoTemplate()) {
            $header = new HeaderService();
            $data['header'] = $header->display($this->breadcrumb);
        }
        new AcycView($this->name, $this->layout, $data);
    }

    public function cancel()
    {
        $this->layout = 'default';
        $this->display();
    }

    public function defaultTask()
    {
        $this->layout = 'default';

        $this->display();
    }

    public function edit()
    {
        $this->layout = 'edit';

        $this->display();
    }

    public function apply()
    {
        $this->store();

        $this->edit();
    }

    public function add()
    {
        Security::setVar('cid', []);
        Security::setVar('layout', 'form');

        $this->display();
    }

    public function save()
    {
        if (method_exists($this, 'store')) {
            $this->store();
        }

        $this->defaultTask();
    }

    public function delete()
    {
        Form::checkToken();
        $ids = Security::getVar('array', 'elements_checked', []);
        $allChecked = Security::getVar('string', 'checkbox_all');
        $currentPage = explode('_', Security::getVar('string', 'page'));
        $pageNumber = Security::getVar('int', end($currentPage).'_pagination_page');

        if (!empty($ids) && !empty($this->currentClass)) {
            $this->currentClass->delete($ids);
            if ($allChecked == 'on') {
                Security::setVar(end($currentPage).'_pagination_page', $pageNumber - 1);
            }
        }

        if (!Security::getVar('bool', 'no_listing', false)) $this->defaultTask();
    }


    protected function getVarFiltersListing($type, $varName, $default, $overrideIfNull = false)
    {
        $returnValue = Security::getVar($type, $varName);

        if (is_null($returnValue) && $overrideIfNull) $returnValue = $default;

        if (!is_null($returnValue)) {
            $_SESSION[$this->sessionName][$varName] = $returnValue;

            return $returnValue;
        }

        if (!empty($_SESSION[$this->sessionName][$varName])) return $_SESSION[$this->sessionName][$varName];

        return $default;
    }
}
