<?php

namespace AcyChecker\Libraries;

use AcyChecker\Classes\BlockUserClass;
use AcyChecker\Classes\DeleteUserClass;
use AcyChecker\Classes\TestClass;
use AcyChecker\Services\ApiService;
use AcyChecker\Services\DebugService;
use AcyCheckerCmsServices\Database;

class AcycIntegrationClass extends AcycClass
{
    /**
     * @var BlockUserClass
     */
    protected $blockUserClass;

    /**
     * @var DeleteUserClass
     */
    protected $deleteUserClass;

    public function __construct()
    {
        parent::__construct();
        $this->blockUserClass = new BlockUserClass();
        $this->deleteUserClass = new DeleteUserClass();
    }

    public function handleBatchCallback($actionSelected, $conditionsSelected, $emailsResults, &$allTests)
    {
        // We get all the emails in the test
        $rawEmails = array_column($emailsResults, ApiService::API_TEST_KEY_EMAIL);
        if (empty($rawEmails)) return;

        $users = $this->getUsersFromEmails($rawEmails);
        if (empty($users)) return;

        $apiService = new ApiService();
        $this->blockUserClass->block_action = BlockUserClass::BLOCK_ACTION_BATCH;
        $this->deleteUserClass->delete_action = DeleteUserClass::DELETE_ACTION_BATCH;

        // We check if we have to block users of not
        foreach ($users as $user) {
            $arrayKey = array_search($user->email, $rawEmails);
            $emailOk = $apiService->isEmailAccepted($emailsResults[$arrayKey], $conditionsSelected);
            if ($emailOk === true) continue;

            if ($actionSelected === 'delete_users') {
                $this->deleteOneUser($user, $emailOk);
            } else {
                $this->blockOneUser($user, $emailOk);
            }

            $allTests[$user->email]['block_reason'] = Database::escapeDB($emailOk);
        }
    }

    public function blockUsers($emails, &$blockedUsers)
    {
        $users = $this->getUsersFromEmails($emails);
        if (empty($users)) return;

        $this->blockUserClass->block_action = BlockUserClass::BLOCK_ACTION_MANUAL;
        foreach ($users as $user) {
            if ($this->blockOneUser($user, 'manual')) {
                $blockedUsers[$user->email] = true;
            }
        }

        $testClass = new TestClass();
        $testClass->setBlockReason(array_column($users, 'email'), 'manual');
    }

    public function deleteUsers($emails, &$deletedUsers)
    {
        $users = $this->getUsersFromEmails($emails);
        if (empty($users)) return;

        $this->deleteUserClass->delete_action = DeleteUserClass::DELETE_ACTION_MANUAL;
        foreach ($users as $user) {
            if ($this->deleteOneUser($user, 'manual')) {
                $deletedUsers[$user->email] = true;
            }
        }

        $testClass = new TestClass();
        $testClass->setBlockReason(array_column($users, 'email'), 'manual');
    }
}
