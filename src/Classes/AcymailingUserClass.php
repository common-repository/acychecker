<?php

namespace AcyChecker\Classes;

use AcyChecker\Libraries\AcycIntegrationClass;
use AcyChecker\Services\DebugService;
use AcyChecker\Services\SecurityService;
use AcyCheckerCmsServices\Database;
use AcyCheckerCmsServices\Extension;
use AcyMailing\Classes\UserClass;

class AcymailingUserClass extends AcycIntegrationClass
{
    private $acyUserClass;

    public function countUsers()
    {
        return Database::loadResult('SELECT COUNT(*) FROM #__acym_user');
    }

    public function getLastYearUsers()
    {
        $lastYear = date('Y-m-d H:i', strtotime('-1 year'));

        return Database::loadResult('SELECT COUNT(*) FROM #__acym_user WHERE creation_date > '.Database::escapeDB($lastYear));
    }

    public function getUsersEmail($offset = 0, $limit = 5000, $fromCron = false, $onlyNew = false, $lists = [])
    {
        // We filter the users if we need to
        $filters = [];
        if ($onlyNew) $filters[] = ' email NOT IN (SELECT email FROM #__acyc_test)';
        if ($fromCron) $filters[] = 'user.active = 1';

        $query = 'SELECT user.email from #__acym_user AS user ';

        // If we selected list we only select users subscribed to it
        if (!empty($lists)) {
            SecurityService::arrayToInteger($lists);
            $query .= 'JOIN #__acym_user_has_list AS user_list ON user.id = user_list.user_id AND user_list.list_id IN ('.implode(',', $lists).')';
        }

        $queryWhere = empty($filters) ? '' : ' WHERE ('.implode(') AND (', $filters).')';

        $query .= ' '.$queryWhere.' LIMIT '.$offset.','.$limit;

        return Database::loadResultArray($query);
    }

    public function unblockUsers($emails, &$unblockedUsers)
    {
        $users = $this->getUsersFromEmails($emails);
        if (empty($users)) return;

        $emails = array_column($users, 'email');
        $safeEmails = array_map('AcyCheckerCmsServices\Database::escapeDB', $emails);

        try {
            Database::query('UPDATE #__acym_user SET active = 1 WHERE email IN ('.implode(',', $safeEmails).')');

            $testClass = new TestClass();
            $testClass->setBlockReason($emails, null);

            foreach ($emails as $email) {
                $unblockedUsers[$email] = true;
            }
        } catch (\Exception $exception) {
            DebugService::logMessage('callback_controller.log', 'Error on request to unblock AcyMailing users - '.$exception->getMessage());
        }
    }

    public function getUserLink($email): string
    {
        $user = $this->getUsersFromEmails([$email]);

        if (empty($user) || !function_exists('acym_completeLink')) {
            return '';
        }

        return acym_completeLink('users&task=edit&userId='.$user[0]->id);
    }

    protected function blockOneUser($user, $reason)
    {
        $this->blockUserClass->recordBlockAction($user->email, $reason);

        try {
            return Database::query('UPDATE #__acym_user SET active = 0 WHERE email = '.Database::escapeDB($user->email));
        } catch (\Exception $exception) {
            DebugService::logMessage('callback_controller.log', 'Error on request to block AcyMailing users - '.$exception->getMessage());
        }

        return false;
    }

    protected function deleteOneUser($user, $reason)
    {
        if (empty($this->acyUserClass)) {
            if (!Extension::isExtensionActive(ACYC_CMS === 'joomla' ? 'com_acym' : 'acymailing/index.php')) {
                return false;
            }

            if (!defined('ACYM_DBPREFIX')) {
                if (ACYC_CMS === 'joomla') {
                    include_once rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_acym'.DS.'helpers'.DS.'helper.php';
                } else {
                    include_once WP_PLUGIN_DIR.DS.'acymailing'.DS.'back'.DS.'helpers'.DS.'helper.php';
                }
            }
            $this->acyUserClass = new \AcyMailing\Classes\UserClass();
        }

        $this->deleteUserClass->recordDeleteAction($user->email, $reason);

        return $this->acyUserClass->delete($user->id, true);
    }

    protected function getUsersFromEmails($emails)
    {
        if (empty($emails)) {
            return [];
        }

        $emails = array_map('AcyCheckerCmsServices\Database::escapeDB', $emails);

        try {
            return Database::loadObjectList('SELECT * FROM #__acym_user WHERE email IN ('.implode(',', $emails).')');
        } catch (\Exception $exception) {
            DebugService::logMessage('callback_controller.log', 'Error on getting AcyMailing users from results - '.$exception->getMessage());

            return [];
        }
    }
}
