<?php

namespace AcyChecker\Controllers;

use AcyChecker\Classes\AcymailingListClass;
use AcyChecker\Classes\Acymailing5ListClass;
use AcyChecker\Libraries\AcycController;
use AcyChecker\Services\DatabaseService;
use AcyChecker\Services\CronService;
use AcyCheckerCmsServices\Form;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Message;
use AcyCheckerCmsServices\Security;
use AcyCheckerCmsServices\Url;
use AcyCheckerCmsServices\User;

class DatabaseController extends AcycController
{
    const NOW = 'now';
    const WEEK = 'week';
    const MONTH = 'month';

    const ACTION_PERIOD = [
        self::NOW,
        self::WEEK,
        self::MONTH,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->name = 'Database';
    }

    public function defaultTask()
    {
        $this->layout = 'default';

        $data = [
            'tables_select' => DatabaseService::getTablesForSelect(),
            'action_select' => DatabaseService::getActionsForSelect(),
            'condition_select' => DatabaseService::getConditionsForSelect(),
        ];

        $this->prepareExecutionSelect($data);
        $this->prepareCurrentConfig($data);

        $data['allow_stop_periodic'] = !empty($data['current_config']['tables_selected']) && in_array($data['current_config']['execution_selected'], [self::WEEK, self::MONTH]);
        $data['tables_filters'] = [
            'cms' => [
                'values' => [],
                'text' => 'CMS',
            ],
        ];
        $this->prepareCmsGroupFilter($data);

        if ($this->isAcymailingInstalled) {
            $data['tables_filters']['acymailing'] = [
                'values' => [],
                'text' => 'AcyMailing',
            ];
            $this->prepareAcyMailingListFilter($data);
        }

        if ($this->isAcymailing5Installed) {
            $data['tables_filters']['acymailing5'] = [
                'values' => [],
                'text' => 'AcyMailing 5',
            ];
            $this->prepareAcyMailing5ListFilter($data);
        }

        $this->breadcrumb[__('Clean my database', 'acychecker')] = Url::completeLink('database');

        $this->display($data);
    }

    private function prepareExecutionSelect(&$data)
    {
        $data['execution_select'] = [
            self::NOW => [
                'value' => self::NOW,
                'text' => __('Right now', 'acychecker'),
            ],
            self::WEEK => [
                'value' => self::WEEK,
                'text' => __('Every week', 'acychecker'),
            ],
            self::MONTH => [
                'value' => self::MONTH,
                'text' => __('Every month', 'acychecker'),
            ],
        ];
    }

    private function prepareCmsGroupFilter(&$data)
    {
        $cmsGroups = User::getGroups();

        foreach ($cmsGroups as $group) {
            $data['tables_filters']['cms']['values'][$group->id] = $group->text;
        }
    }

    private function prepareAcyMailingListFilter(&$data)
    {
        if ($this->isAcymailingInstalled) {
            $acymailingListClass = new AcymailingListClass();
            $data['tables_filters']['acymailing']['values'] = $acymailingListClass->getAllListsForSelect();
        }
    }

    private function prepareAcyMailing5ListFilter(&$data)
    {
        if ($this->isAcymailing5Installed) {
            $acymailing5ListClass = new Acymailing5ListClass();
            $data['tables_filters']['acymailing5']['values'] = $acymailing5ListClass->getAllListsForSelect();
        }
    }

    private function prepareCurrentConfig(&$data)
    {
        $data['current_config'] = [];
        $configurations = [
            'tables_selected',
            'table_filter_cms',
            'table_filter_acymailing',
            'table_filter_acymailing5',
            'conditions_selected',
        ];

        foreach ($configurations as $oneConfiguration) {
            $data['current_config'][$oneConfiguration] = $this->config->get($oneConfiguration);
            if (empty($data['current_config'][$oneConfiguration])) {
                $data['current_config'][$oneConfiguration] = [];
            } else {
                $data['current_config'][$oneConfiguration] = explode(',', $data['current_config'][$oneConfiguration]);
            }
        }

        $data['current_config']['action_selected'] = $this->config->get('action_selected');
        if (empty($data['current_config']['action_selected'])) {
            $data['current_config']['action_selected'] = 'block_users';
        }
        $data['current_config']['execution_selected'] = $this->config->get('execution_selected');
        if (!in_array($data['current_config']['execution_selected'], self::ACTION_PERIOD)) {
            $data['current_config']['execution_selected'] = self::NOW;
        }
    }

    protected function store()
    {
        $savedConfig = Security::getVar('array', 'acyc_config');
        $newConfig = [
            'tables_selected' => isset($savedConfig['tables_selected']) ? $savedConfig['tables_selected'] : [],
            'table_filter_cms' => isset($savedConfig['table_filter_cms']) ? $savedConfig['table_filter_cms'] : [],
            'table_filter_acymailing' => isset($savedConfig['table_filter_acymailing']) ? $savedConfig['table_filter_acymailing'] : [],
            'table_filter_acymailing5' => isset($savedConfig['table_filter_acymailing5']) ? $savedConfig['table_filter_acymailing5'] : [],
            'conditions_selected' => isset($savedConfig['conditions_selected']) ? $savedConfig['conditions_selected'] : [],
            'execution_selected' => isset($savedConfig['execution_selected']) ? $savedConfig['execution_selected'] : self::NOW,
            'action_selected' => isset($savedConfig['action_selected']) ? $savedConfig['action_selected'] : 'block_users',
        ];

        $this->config->save($newConfig);
    }

    public function save()
    {
        Form::checkToken();
        $this->store();
        Message::enqueueMessage(__('Configuration saved', 'acychecker'));

        $this->defaultTask();
    }

    public function process()
    {
        Form::checkToken();
        $this->store();

        $cronService = new CronService();
        $cronService->processBatches();

        $this->defaultTask();
    }

    public function stop()
    {
        Form::checkToken();

        $newConfig = [
            'tables_selected' => [],
            'execution_selected' => self::NOW,
        ];

        $this->config->save($newConfig);
        Message::enqueueMessage(__('Configuration saved', 'acychecker'));

        $this->defaultTask();
    }
}
