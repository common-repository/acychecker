<?php


namespace AcyChecker\Services;


use AcyChecker\Classes\BlockUserClass;
use AcyChecker\Classes\TestClass;
use AcyChecker\Libraries\AcycObject;
use AcyCheckerCmsServices\Database;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Message;
use AcyCheckerCmsServices\Url;


class ApiService extends AcycObject
{
    private $apiBaseUrl;
    public $errors = [];
    private $apiKey;
    private $blacklistedRules = [];
    private $whitelistedRules = [];

    const API_TEST_KEY_EMAIL = 'email';
    const API_TEST_KEY_VALID_ADDRESS = 'valid';
    const API_TEST_KEY_DISPOSABLE = 'disposable';
    const API_TEST_KEY_FREE_MAILBOX = 'free';
    const API_TEST_KEY_ROLE_BASED = 'role';
    const API_TEST_KEY_DOMAIN_EXISTS = 'd_exists';
    const API_TEST_KEY_DOMAIN_SUGGESTIONS = 'suggestions';
    const API_TEST_KEY_EMAIL_EXISTS = 'exists';
    const API_TEST_KEY_ACCEPT_ALL = 'accept_all';

    public function __construct()
    {
        parent::__construct();

        $this->apiBaseUrl = ACYC_API_URL.'api/v1/';
        $this->config->load();
        $this->apiKey = $this->config->get('license_key');
        $blacklisted = $this->config->get('blacklist');
        if (!empty($blacklisted)) {
            $this->blacklistedRules = explode(',', $blacklisted);
            $this->blacklistedRules = array_map('trim', $this->blacklistedRules);
        }

        $whitelisted = $this->config->get('whitelist');
        if (!empty($whitelisted)) {
            $this->whitelistedRules = explode(',', $whitelisted);
            $this->whitelistedRules = array_map('trim', $this->whitelistedRules);
        }
    }

    private function processRequest($url, $header, $data = [], $type = 'GET', $options = [])
    {
        try {
            $options['verify'] = false;
            if (class_exists('\WpOrg\Requests\Requests')) {
                $request = \WpOrg\Requests\Requests::request($url, $header, $type == 'GET' ? $data : json_encode($data), $type, $options);
            } else {
                $request = \Requests::request($url, $header, $type == 'GET' ? $data : json_encode($data), $type, $options);
            }
        } catch (\Exception $exception) {
            $this->errors[] = $exception->getMessage();

            return false;
        }

        $request->body = json_decode($request->body, true);

        return $request;
    }

    private function getDefaultHeaders()
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'API-KEY' => $this->apiKey,
            'Source' => 'plugin',
            'Source-Version' => '1.4.0',
        ];
    }

    public function getCredits()
    {
        $url = $this->apiBaseUrl.'licenses/credits';

        $request = $this->processRequest($url, $this->getDefaultHeaders());
        if (!empty($this->errors)) {
            Message::enqueueMessage(implode(', ', $this->errors), 'error');
        }

        if (!$request) {
            return $this->returnResponse(__('Could not call the api', 'acychecker'), [], false);
        }

        if ($request->status_code !== 200) {
            if (isset($request->body['message'])) {
                return $this->returnResponse($request->body['message'], [], false);
            } else {
                return $this->returnResponse(
                    sprintf(__('Error while calling the API (code %s)', 'acychecker'), $request->status_code),
                    [],
                    false
                );
            }
        }


        return $this->returnResponse('', $request->body);
    }

    public function dispatchBatch($emails)
    {
        if (empty($emails)) {
            return $this->returnResponse(__('Invalid email address', 'acychecker'), [], false);
        }

        $url = $this->apiBaseUrl.'bulk_verify/';

        $data = [
            'emails' => $emails,
            'callbackUrl' => Url::frontendLink('callbackController'),
            'fileType' => 'CSV',
        ];

        $request = $this->processRequest($url, $this->getDefaultHeaders(), $data, 'POST');

        if (!$request) {
            return $this->returnResponse(__('Could not call the api', 'acychecker'), [], false);
        }

        //Not enough credits
        if ($request->status_code === 403 && strpos($request->body['message'], 'Not enough credits') !== false) {
            return $this->returnResponse(__('Not enough credits to run this test', 'acychecker'), [], false);
        } elseif ($request->status_code !== 201) {
            return $this->returnResponse($request->body['message'], [], false);
        }

        return $this->returnResponse('', $request->body);
    }

    private function returnResponse($message = '', $data = [], $success = true)
    {
        return [
            'message' => $message,
            'data' => $data,
            'success' => $success,
        ];
    }

    public function testEmail($email, $conditions)
    {
        $result = $this->getEmailResult($email);

        if ($result['success'] === false) {
            // An error occurred while testing the email address, no credits left for example, don't block the subscription
            return true;
        }

        $conditions = explode(',', $conditions);

        $emailOk = $this->isEmailAccepted($result['data'], $conditions);

        $this->saveTest($result['data'], $emailOk);

        return $emailOk;
    }

    public function isEmailAccepted($emailResult, $conditions)
    {
        if ($this->isMatchingRules($emailResult[self::API_TEST_KEY_EMAIL], $this->whitelistedRules)) {
            return true;
        }

        if (in_array('domain_not_exists', $conditions) && intval($emailResult[self::API_TEST_KEY_DOMAIN_EXISTS]) !== 1) {
            return 'domain_not_exists';
        }

        if (in_array('invalid_smtp', $conditions) && $emailResult[self::API_TEST_KEY_EMAIL_EXISTS] === 'not_existing') {
            return 'invalid_smtp';
        }

        foreach (
            [
                'disposable' => self::API_TEST_KEY_DISPOSABLE,
                'accept_all' => self::API_TEST_KEY_ACCEPT_ALL,
                'free_domain' => self::API_TEST_KEY_FREE_MAILBOX,
                'role_based' => self::API_TEST_KEY_ROLE_BASED,
            ] as $acycName => $apiName
        ) {
            if (in_array($acycName, $conditions) && intval($emailResult[$apiName]) === 1) {
                return $acycName;
            }
        }

        if ($this->isMatchingRules($emailResult[self::API_TEST_KEY_EMAIL], $this->blacklistedRules)) {
            return 'blacklisted';
        }

        return true;
    }

    private function getEmailResult($email)
    {
        $url = $this->apiBaseUrl.'email_verify/'.$email;

        $request = $this->processRequest($url, $this->getDefaultHeaders());

        if (!$request) {
            $message = __('Could not call the api', 'acychecker');
            if (!empty($this->errors)) $message = implode(', ', $this->errors);
            DebugService::logMessage('individual_tests.log', $email.' - '.$message);

            return [
                'success' => false,
                'message' => __('Could not call the api', 'acychecker'),
            ];
        }

        if ($request->status_code !== 200) {
            if (isset($request->body['message'])) {
                $message = $request->body['message'];
            } else {
                $message = sprintf(__('Error while calling the API (code %s)', 'acychecker'), $request->status_code);
            }
            DebugService::logMessage('individual_tests.log', $email.' - '.$message);

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        return [
            'success' => true,
            'data' => $request->body['data'],
        ];
    }

    public function prepareTest($result, $escape)
    {
        $dateTimeNow = new \DateTime('NOW');
        $dateTimeNow->setTimezone(new \DateTimeZone('UTC'));
        $testClass = new TestClass();

        $test = new \stdClass();
        $test->email = $result[self::API_TEST_KEY_EMAIL];
        $test->date = $dateTimeNow->format('Y-m-d H:i:s');
        $test->raw_result = json_encode($result);
        $test->test_result = $result[self::API_TEST_KEY_EMAIL_EXISTS];

        if ($escape) {
            $test->email = Database::escapeDB($test->email);
            $test->date = Database::escapeDB($test->date);
            $test->raw_result = Database::escapeDB($test->raw_result);
            $test->test_result = Database::escapeDB($test->test_result);
        }

        $test->disposable = $result[self::API_TEST_KEY_DISPOSABLE] ? 1 : 0;
        $test->free = $result[self::API_TEST_KEY_FREE_MAILBOX] ? 1 : 0;
        $test->accept_all = $result[self::API_TEST_KEY_ACCEPT_ALL] ? 1 : 0;
        $test->role_email = $result[self::API_TEST_KEY_ROLE_BASED] ? 1 : 0;
        $test->current_step = $testClass::STEP['finished'];
        $test->domain_exists = $result[self::API_TEST_KEY_DOMAIN_EXISTS] ? 1 : 0;

        return $test;
    }

    private function saveTest($result, $emailOk)
    {
        $test = $this->prepareTest($result, false);
        $test->block_reason = $emailOk === true ? null : $emailOk;
        $test->batch_id = null;

        $testClass = new TestClass();
        $testClass->save($test);

        if ($emailOk !== true) {
            $blockedEmail = new \stdClass();
            $blockedEmail->email = $test->email;
            $blockedEmail->block_reason = $emailOk;

            $blockUserClass = new BlockUserClass();
            $blockUserClass->block_action = BlockUserClass::BLOCK_ACTION_REGISTRATION;
            $blockUserClass->save($blockedEmail);
        }
    }

    public function refreshCreditsUsed()
    {
        $result = $this->getCredits();

        if ($result['success']) {
            $newConfig = [
                'credits_used_batch' => $result['data']['credits_used_batch'],
                'remaining_credits_batch' => $result['data']['remaining_credits_batch'],
                'credits_used_simple' => $result['data']['credits_used_simple'],
                'remaining_credits_simple' => $result['data']['remaining_credits_simple'],
                'license_last_check' => time(),
                'license_end_date' => $result['data']['end_date'],
            ];
            $this->config->save($newConfig);
        } else {
            Message::enqueueMessage($result['message'], 'error');
        }
    }

    public function getBatchResult($batchId)
    {
        if (empty($batchId)) return false;

        $url = $this->apiBaseUrl.'bulk_result/'.$batchId;

        $request = $this->processRequest($url, $this->getDefaultHeaders());

        if (!$request) {
            DebugService::logMessage('batch_tests.log', 'Error getting '.$batchId.' results');

            return false;
        }

        if ($request->status_code !== 200) {
            $codes = [
                403 => 'The API key is incorrect',
                404 => 'The results are unavailable',
                429 => 'Too many requests',
            ];
            if (in_array($request->status_code, array_keys($codes))) {
                DebugService::logMessage('batch_tests.log', 'Error getting '.$batchId.' results: '.$codes[$request->status_code]);
            } else {
                DebugService::logMessage('batch_tests.log', 'Error getting '.$batchId.' results: Unknown return code '.$request->status_code);
            }

            return false;
        }

        if (empty($request->body['data'])) {
            DebugService::logMessage('batch_tests.log', 'Error getting '.$batchId.' results: No data returned');

            return false;
        }

        return $request->body['data'];
    }

    private function isMatchingRules($email, $rules): bool
    {
        if (empty($rules)) {
            return false;
        }

        $reversedEmail = strrev($email);
        foreach ($rules as $oneRule) {
            if (strpos($reversedEmail, strrev($oneRule)) === 0) {
                return true;
            }

            if (strpos($oneRule, '$') !== false && preg_match('/'.$oneRule.'/', $email) === 1) {
                return true;
            }
        }

        return false;
    }
}
