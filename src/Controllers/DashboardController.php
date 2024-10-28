<?php

namespace AcyChecker\Controllers;


use AcyChecker\Classes\CmsUserClass;
use AcyChecker\Classes\AcymailingUserClass;
use AcyChecker\Classes\Acymailing5UserClass;
use AcyChecker\Classes\TestClass;
use AcyChecker\Libraries\AcycController;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Message;
use AcyCheckerCmsServices\Url;

class DashboardController extends AcycController
{
    private $disposableEmailDomains;
    private $testClass;

    public function __construct()
    {
        parent::__construct();

        $this->name = 'Dashboard';
        $this->defaultTask = 'dashboard';

        $this->testClass = new TestClass();
    }

    public function dashboard()
    {
        // Build infos block
        $data = [];
        $this->buildInfo($data);
        $this->prepareDonutStatistics($data);
        $this->prepareDonutBlockedUsers($data);
        $this->prepareLineChart($data);

        $this->layout = 'default';

        $this->breadcrumb[__('Dashboard', 'acychecker')] = Url::completeLink('dashboard');

        $this->display($data);
    }

    private function buildInfo(&$data)
    {
        $data = [];

        // CMS users
        $cmsUserClass = new CmsUserClass();
        $data['cmsUsers'] = $cmsUserClass->countUsers();
        $data['cmsUsersEvolution'] = number_format($cmsUserClass->getLastYearUsers() / 12, 1);

        // If AcyMailing installed count AcyMailing users
        if ($this->isAcymailingInstalled) {
            $acyMailingUserClass = new AcyMailingUserClass();
            $data['acyUsers'] = $acyMailingUserClass->countUsers();
            $data['acyUsersEvolution'] = number_format($acyMailingUserClass->getLastYearUsers() / 12, 1);
        }

        // If AcyMailing 5 installed count AcyMailing 5 users
        if ($this->isAcymailing5Installed) {
            $acyMailing5UserClass = new AcyMailing5UserClass();
            $data['acy5Users'] = $acyMailing5UserClass->countUsers();
            $data['acy5UsersEvolution'] = number_format($acyMailing5UserClass->getLastYearUsers() / 12, 1);
        }

        // Free or no license: estimation of disposable email addresses
        if (in_array($this->config->get('license_level', ''), ['', 'AcyChecker-Starter'])) {
            $this->getDisposableList();
            $disposableCmsEmails = 0;
            $disposableAcyEmails = 0;

            if (!empty($this->disposableEmailDomains)) {
                $lastCheck = $this->config->get('dashboard_last_disposable_check');
                if (empty($lastCheck) || time() - $lastCheck > 86400) {
                    $newConfig = [
                        'dashboard_last_disposable_check' => time(),
                        'dashboard_disposable_cms' => $this->checkSimpleDisposable($cmsUserClass),
                    ];
                    if ($this->isAcymailingInstalled) {
                        $newConfig['dashboard_disposable_acym'] = $this->checkSimpleDisposable($acyMailingUserClass);
                    }
                    if ($this->isAcymailing5Installed) {
                        $newConfig['dashboard_disposable_acy5'] = $this->checkSimpleDisposable($acyMailing5UserClass);
                    }

                    $this->config->save($newConfig);
                }

                $disposableCmsEmails = $this->config->get('dashboard_disposable_cms', 0);
                if ($this->isAcymailingInstalled) {
                    $disposableAcyEmails = $this->config->get('dashboard_disposable_acym', 0);
                }
            }

            $disposableCmsEmailsPercent = $this->getPercentFakes($data['cmsUsers'], $disposableCmsEmails);
            $data['disposableCmsEmails'] = number_format($disposableCmsEmailsPercent, 2).'%';
            $estimateCmsDisposable = $disposableCmsEmailsPercent * $data['cmsUsers'];

            if ($this->isAcymailingInstalled) {
                $disposableAcyEmailsPercent = $this->getPercentFakes($data['acyUsers'], $disposableAcyEmails);
                $data['disposableAcyEmails'] = number_format($disposableAcyEmailsPercent, 2).'%';
                $estimateAcyDisposable = $disposableAcyEmailsPercent * $data['acyUsers'];
            } else {
                $estimateAcyDisposable = 0;
            }

            $totalDisposableEmails = $estimateCmsDisposable + $estimateAcyDisposable;
            $data['totalDisposableEmails'] = $totalDisposableEmails;

            $totalEmails = $this->isAcymailingInstalled ? $data['cmsUsers'] + $data['acyUsers'] : $data['cmsUsers'];
            if ($totalEmails < 1000) {
                $data['suggestedPlan'] = 'Freelancer';
            } elseif ($totalEmails < 5000) {
                $data['suggestedPlan'] = 'Marketer';
            } else {
                $data['suggestedPlan'] = 'Agency';
            }

            // Add a message to enter the license key
            Message::enqueueMessage(
                sprintf(__('To use AcyChecker, you need to enter your license key in the %s', 'acychecker'), '<a href="'.Url::completeLink('configuration').'">'.__('Configuration', 'acychecker').'</a>'),
                'warning'
            );
        }
    }

    public function checkSimpleDisposable($specificUserClass)
    {
        $emails = $specificUserClass->getUsersEmail();
        $disposableEmails = 0;
        foreach ($emails as $oneEmail) {
            $domain = substr($oneEmail, strpos($oneEmail, '@') + 1);
            if (in_array($domain, $this->disposableEmailDomains)) {
                $disposableEmails++;
            }
        }

        return $disposableEmails;
    }

    protected function getPercentFakes($nbTotalEmails, $nbFakes, $max = 5000)
    {
        if ($nbTotalEmails === 0) {
            return 0;
        }
        $nbTests = min($nbTotalEmails, $max);

        return $nbFakes * 100 / $nbTests;
    }

    protected function getDisposableList()
    {
        if (!empty($this->disposableEmailDomains)) {
            return;
        }

        $disposableListFile = ACYC_VAR.'disposable.txt';
        if (!file_exists($disposableListFile)) {
            return;
        }
        $this->disposableEmailDomains = explode("\n", file_get_contents($disposableListFile));
    }

    private function prepareDonutBlockedUsers(&$data)
    {
        $blockReason = $this->testClass->getBlockedUsersRepartition();

        if (empty($blockReason)) {
            $data['emptyStatsBlocked'] = true;
            $disposable = new \stdClass();
            $disposable->block_reason = 'disposable';
            $disposable->value = 185;

            $domainNotExists = new \stdClass();
            $domainNotExists->block_reason = 'domain_not_exists';
            $domainNotExists->value = 954;

            $acceptAll = new \stdClass();
            $acceptAll->block_reason = 'accept_all';
            $acceptAll->value = 2574;

            $invalidSmtp = new \stdClass();
            $invalidSmtp->block_reason = 'invalid_smtp';
            $invalidSmtp->value = 1954;

            $blacklisted = new \stdClass();
            $blacklisted->block_reason = 'blacklisted';
            $blacklisted->value = 230;

            $manual = new \stdClass();
            $manual->block_reason = 'manual';
            $manual->value = 1280;

            $blockReason = [
                $disposable,
                $domainNotExists,
                $acceptAll,
                $invalidSmtp,
                $blacklisted,
                $manual,
            ];
        }

        $blockedUsersOptions = $this->formatBlockedValuesToPercentage($blockReason);

        $data['block_reason'] = json_encode($blockedUsersOptions);
    }

    private function formatBlockedValuesToPercentage($blockReason)
    {
        $total = 0;
        foreach ($blockReason as $one) {
            $total += $one->value;
        }

        foreach ($blockReason as $key => $one) {
            $blockReason[$key]->value = empty($total) ? 0 : round($one->value * 100 / $total);
        }

        return ['values' => $blockReason, 'total' => $total];
    }

    private function prepareDonutStatistics(&$data)
    {
        $numberOfTest = intval($this->testClass->getNbResults());

        $numberOfDisposable = empty($numberOfTest) ? 185 : $this->testClass->getTotalDisposable();
        $numberOfFree = empty($numberOfTest) ? 9873 : $this->testClass->getTotalFree();
        $numberOfAcceptAll = empty($numberOfTest) ? 873 : $this->testClass->getTotalAcceptAll();
        $numberOfRoleEmail = empty($numberOfTest) ? 476 : $this->testClass->getTotalRoleEmail();

        if (empty($numberOfTest)) {
            $data['emptyStats'] = true;
            $numberOfTest = 10000;
        }

        $donutData = [];

        $donutData['disposablePercentage'] = [
            'nameKey' => 'disposable',
            'value' => empty($numberOfDisposable) ? 0 : round(intval($numberOfDisposable) * 100 / $numberOfTest, 2),
        ];
        $donutData['freePercentage'] = [
            'nameKey' => 'free',
            'value' => empty($numberOfFree) ? 0 : round(intval($numberOfFree) * 100 / $numberOfTest, 2),
        ];
        $donutData['acceptAllPercentage'] = [
            'nameKey' => 'accept_all',
            'value' => empty($numberOfAcceptAll) ? 0 : round(intval($numberOfAcceptAll) * 100 / $numberOfTest, 2),
        ];
        $donutData['roleEmailPercentage'] = [
            'nameKey' => 'role_email',
            'value' => empty($numberOfRoleEmail) ? 0 : round(intval($numberOfRoleEmail) * 100 / $numberOfTest, 2),
        ];

        $data['donutData'] = $donutData;
    }

    private function prepareLineChart(&$data)
    {
        $numberOfTest = $this->testClass->getNumberOfTestByDay();

        $days = [];
        for ($i = 1 ; $i <= date('d', time()) ; $i++) {
            $day = $i < 10 ? '0'.$i : $i;
            $date = date('Y-m-'.$day, time());
            if (!empty($data['emptyStats'])) {
                $days[$date] = rand(1000, 10000);
            } else {
                $days[$date] = empty($numberOfTest[$date]->count) ? 0 : $numberOfTest[$date]->count;
            }
        }

        $data['month_calls'] = $days;
    }
}
