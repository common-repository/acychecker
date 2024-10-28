<?php

namespace AcyChecker\Classes;

use AcyChecker\Libraries\AcycIntegrationClass;
use AcyChecker\Services\DebugService;
use AcyChecker\Services\SecurityService;
use AcyCheckerCmsServices\Database;
use AcyCheckerCmsServices\User;

class CmsUserClass extends AcycIntegrationClass
{
    public function countUsers()
    {
        return Database::loadResult('SELECT COUNT(*) FROM '.$this->cmsUserVars->table);
    }

    public function getLastYearUsers()
    {
        $lastYear = date('Y-m-d H:i', strtotime('-1 year'));

        return Database::loadResult('SELECT COUNT(*) FROM '.$this->cmsUserVars->table.' WHERE '.$this->cmsUserVars->registered.' > '.Database::escapeDB($lastYear));
    }

    public function getUsersEmail($offset = 0, $limit = 5000, $fromCron = false, $onlyNew = false, $groups = [])
    {
        // We filter the users if we need to
        $filters = [];
        if ($onlyNew) $filters[] = $this->cmsUserVars->email.' NOT IN (SELECT email FROM #__acyc_test)';

        $query = 'SELECT '.$this->cmsUserVars->email.' FROM '.$this->cmsUserVars->table.' AS user';

        // If we selected groups we only select users belonging to it
        $query .= $this->getGroupsFilterForQuery($groups);

        $queryWhere = empty($filters) ? '' : ' WHERE ('.implode(') AND (', $filters).')';

        $query .= ' '.$queryWhere.' LIMIT '.$offset.','.$limit;

        return Database::loadResultArray($query);
    }

    private function getGroupsFilterForQuery($groups)
    {
        if (ACYC_CMS === 'joomla') {
            SecurityService::arrayToInteger($groups);
            $query = ' JOIN #__user_usergroup_map AS group_map ON group_map.user_id = user.id AND group_map.group_id';
            // Don't select Super Users
            $query .= empty($groups) ? ' != '.ACYC_ADMIN_GROUP : ' IN ('.implode(',', $groups).')';
        } else {
            $query = ' JOIN #__usermeta AS usermeta 
                            ON usermeta.user_id = user.ID 
                            AND usermeta.meta_key = "#__capabilities" 
                            AND ';
            if (empty($groups)) {
                // Don't select Super Users
                $query .= 'usermeta.meta_value NOT LIKE '.Database::escapeDB('%"'.ACYC_ADMIN_GROUP.'"%');
            } else {
                $escapedGroups = [];
                foreach ($groups as $oneGroupName) {
                    $escapedGroups[] = Database::escapeDB('%"'.$oneGroupName.'"%');
                }
                $query .= '(usermeta.meta_value LIKE '.implode(' OR usermeta.meta_value LIKE ', $escapedGroups).')';
            }
        }

        return $query;
    }

    public function unblockUsers($emails, &$unblockedUsers)
    {
        $users = $this->getUsersFromEmails($emails);
        if (empty($users)) return;

        $blockIds = array_column($users, 'id');

        if (ACYC_CMS == 'joomla') {
            $blockQuery = 'UPDATE '.$this->cmsUserVars->table.' SET block = 0 WHERE '.$this->cmsUserVars->id.' IN ('.implode(',', $blockIds).')';
        } else {
            $blockQuery = 'UPDATE #__usermeta 
                            SET meta_value = '.Database::escapeDB(serialize([get_option('default_role') => true])).' 
                            WHERE meta_key = "#__capabilities" 
                                AND meta_value = "a:0:{}" 
                                AND user_id IN ('.implode(',', $blockIds).')';
        }

        try {
            Database::query($blockQuery);

            $emails = array_column($users, 'email');
            $testClass = new TestClass();
            $testClass->setBlockReason($emails, null);

            foreach ($emails as $email) {
                $unblockedUsers[$email] = true;
            }
        } catch (\Exception $exception) {
            DebugService::logMessage('callback_controller.log', 'Error on request to unblock CMS users - '.$exception->getMessage());
        }
    }

    public function getUserLink($email): string
    {
        $user = $this->getUsersFromEmails([$email]);

        if (empty($user)) {
            return '';
        }

        return User::getCmsUserEdit($user[0]->id);
    }

    protected function blockOneUser($cmsUser, $reason)
    {
        $this->blockUserClass->recordBlockAction($cmsUser->email, $reason);

        if (User::currentUserId() === $cmsUser->id) {
            return false;
        }

        $groups = User::getGroupsByUser($cmsUser->id);
        if (in_array(ACYC_ADMIN_GROUP, $groups)) {
            return false;
        }

        if (ACYC_CMS === 'joomla') {
            $blockQuery = 'UPDATE '.$this->cmsUserVars->table.' SET block = 1 WHERE '.$this->cmsUserVars->id.' = '.intval($cmsUser->id);
        } else {
            $blockQuery = 'UPDATE #__usermeta SET meta_value = "a:0:{}" WHERE meta_key = "#__capabilities" AND user_id = '.intval($cmsUser->id);
        }

        try {
            return Database::query($blockQuery);
        } catch (\Exception $exception) {
            DebugService::logMessage('callback_controller.log', 'Error on request to block CMS users - '.$exception->getMessage());
        }

        return false;
    }

    protected function deleteOneUser($cmsUser, $reason)
    {
        $this->deleteUserClass->recordDeleteAction($cmsUser->email, $reason);

        if (User::currentUserId() === $cmsUser->id) {
            return false;
        }

        $groups = User::getGroupsByUser($cmsUser->id);
        if (in_array(ACYC_ADMIN_GROUP, $groups)) {
            return false;
        }

        if (ACYC_CMS === 'joomla') {
            $user = \Joomla\CMS\User\User::getInstance($cmsUser->id);
            $user->delete();
        } else {
            if (!function_exists('wp_delete_user')) {
                require_once ABSPATH.'wp-admin/includes/user.php';
            }

            wp_delete_user($cmsUser->id);
        }

        return true;
    }

    protected function getUsersFromEmails($emails)
    {
        if (empty($emails)) {
            return [];
        }

        $emails = array_map('AcyCheckerCmsServices\Database::escapeDB', $emails);

        try {
            return Database::loadObjectList(
                'SELECT '.$this->cmsUserVars->id.' AS id, '.$this->cmsUserVars->email.' AS email 
                FROM '.$this->cmsUserVars->table.' 
                WHERE '.$this->cmsUserVars->email.' IN ('.implode(',', $emails).')'
            );
        } catch (\Exception $exception) {
            DebugService::logMessage('callback_controller.log', 'Error on getting CMS users from results - '.$exception->getMessage());

            return [];
        }
    }
}
