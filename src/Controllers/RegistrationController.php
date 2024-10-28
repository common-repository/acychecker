<?php

namespace AcyChecker\Controllers;

use AcyChecker\Libraries\AcycController;
use AcyChecker\Services\DatabaseService;
use AcyCheckerCmsServices\Form;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Message;
use AcyCheckerCmsServices\Security;
use AcyCheckerCmsServices\Url;

class RegistrationController extends AcycController
{
    public function __construct()
    {
        parent::__construct();

        $this->name = 'Registration';
    }

    public function defaultTask()
    {
        $this->layout = 'default';

        $data = [
            'tables_select' => DatabaseService::getTablesForSelect(),
            'condition_select' => DatabaseService::getConditionsForSelect(),
        ];

        $this->prepareRegistrationConfiguration($data);

        $this->breadcrumb[__('Block on registration', 'acychecker')] = Url::completeLink('registration');

        $this->display($data);
    }

    public function save()
    {
        Form::checkToken();

        $registrationConfig = Security::getVar('array', 'acyc_config', []);
        if (empty($registrationConfig['registration_integrations'])) $registrationConfig['registration_integrations'] = [];
        if (empty($registrationConfig['registration_conditions'])) $registrationConfig['registration_conditions'] = [];

        $this->config->save($registrationConfig);
        $newConfig = [
            'email_verification_disposable' => intval(!empty($registrationConfig['registration_conditions']['disposable'])),
            'email_verification_free' => intval(!empty($registrationConfig['registration_conditions']['free_domain'])),
            'email_verification_role' => intval(!empty($registrationConfig['registration_conditions']['role_based'])),
            'email_verification_acceptall' => intval(!empty($registrationConfig['registration_conditions']['accept_all'])),
            'email_checkdomain' => intval(!empty($registrationConfig['registration_conditions']['domain_not_exists'])),
            'email_verification_non_existing' => intval(!empty($registrationConfig['registration_conditions']['invalid_smtp'])),
        ];
        $this->synchroniseConfigurationWithAcy($registrationConfig, $newConfig);
        $this->synchroniseConfigurationWithAcy5($registrationConfig, $newConfig);

        Message::enqueueMessage(__('Configuration saved', 'acychecker'));
        $this->defaultTask();
    }

    private function synchroniseConfigurationWithAcy($registrationConfig, $newConfig)
    {
        if (!$this->isAcymailingInstalled) return;

        if (ACYC_CMS === 'joomla') {
            include_once rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_acym'.DS.'helpers'.DS.'helper.php';
        } else {
            include_once WP_PLUGIN_DIR.DS.'acymailing'.DS.'back'.DS.'helpers'.DS.'helper.php';
        }

        $acymConfig = acym_config();
        $acymVersion = $acymConfig->get('version');

        if (version_compare($acymVersion, '7.6.0', '<')) return;

        $newConfig['email_verification'] = intval(!empty($registrationConfig['registration_integrations']['acymailing']));

        $acymConfig->save($newConfig);
    }

    private function synchroniseConfigurationWithAcy5($registrationConfig, $newConfig)
    {
        if (!$this->isAcymailing5Installed) return;

        if (ACYC_CMS === 'joomla') {
            include_once rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php';
        } else {
            include_once WP_PLUGIN_DIR.DS.'acymailing5'.DS.'back'.DS.'helpers'.DS.'helper.php';
        }

        $acymConfig = acymailing_config();
        $acymVersion = $acymConfig->get('version');

        if (version_compare($acymVersion, '5.10.26', '<')) return;

        $newConfig['email_verification'] = intval(!empty($registrationConfig['registration_integrations']['acymailing5']));

        $acymConfig->save($newConfig);
    }

    private function prepareRegistrationConfiguration(&$data)
    {
        $data['current_config'] = [
            'registration_integrations' => array_filter(explode(',', $this->config->get('registration_integrations'))),
            'registration_conditions' => explode(',', $this->config->get('registration_conditions')),
        ];
    }

    public function stop()
    {
        Form::checkToken();

        $newConfig = [
            'registration_integrations' => [],
        ];

        $this->config->save($newConfig);
        Message::enqueueMessage(__('Configuration saved', 'acychecker'));

        $this->defaultTask();
    }
}
