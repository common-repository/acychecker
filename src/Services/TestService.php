<?php

namespace AcyChecker\Services;


use AcyChecker\Classes\Acymailing5UserClass;
use AcyChecker\Classes\AcymailingUserClass;
use AcyChecker\Classes\CmsUserClass;
use AcyChecker\Classes\TestClass;
use AcyChecker\Libraries\AcycObject;
use AcyCheckerCmsServices\Database;

class TestService extends AcycObject
{
    const ACTION_BLOCK_USERS = 'block_users';
    const ACTION_DELETE_USERS = 'delete_users';

    public function decodeResult($result)
    {
        if (is_array($result)) {
            return $result;
        }

        if (strpos($result, '{"results":') === 0) {
            return json_decode($result, true);
        }

        if (strpos($result, ApiService::API_TEST_KEY_EMAIL.','.ApiService::API_TEST_KEY_VALID_ADDRESS) === 0) {
            $formattedResults = ['results' => []];
            $rows = explode("\n", $result);
            if (count($rows) < 2) {
                return null;
            }

            $headers = array_shift($rows);
            $headers = explode(',', trim($headers, "\r\n "));
            $headersCount = count($headers);

            foreach ($rows as $oneResult) {
                $oneResult = explode(',', trim($oneResult, "\r\n "));
                if (count($oneResult) !== $headersCount) continue;

                $formattedResults['results'][] = array_combine($headers, $oneResult);
            }

            if (empty($formattedResults['results'])) {
                return null;
            }

            return $formattedResults;
        }

        return null;
    }

    private function getConditions()
    {
        // We get the conditions selected
        $conditionsSelected = $this->config->get('conditions_selected');
        if (empty($conditionsSelected)) return [];

        $conditionsSelected = explode(',', $conditionsSelected);
        if (empty($conditionsSelected)) return [];

        return $conditionsSelected;
    }

    /**
     * @param int          $testId
     * @param string|array $result Either a CSV full content or a decoded json
     *
     * @return void
     */
    public function handleBatchResults($testId, $result)
    {
        $result = $this->decodeResult($result);

        $testClass = new TestClass();
        if (empty($result['results'])) {
            DebugService::logMessage('callback_controller.log', 'The results from the API are empty for the batch ID '.$testId);
            $testClass->setBatchAsFailed($testId);
            $this->sendNextBatch();

            return;
        }

        $apiService = new ApiService();
        $results = $result['results'];

        $allTests = [];

        foreach ($results as $oneResult) {
            $allTests[$oneResult[ApiService::API_TEST_KEY_EMAIL]] = get_object_vars($apiService->prepareTest($oneResult, true));
            $allTests[$oneResult[ApiService::API_TEST_KEY_EMAIL]]['block_reason'] = 'NULL';
        }

        // We get the selected table to check
        $tablesSelected = $this->config->get('tables_selected');
        if (empty($tablesSelected)) {
            DebugService::logMessage('callback_controller.log', 'No table selected in the configuration');
        } else {
            $tablesSelected = explode(',', $tablesSelected);

            $actionSelected = $this->config->get('action_selected');
            if ($actionSelected !== 'do_nothing') {
                $conditionsSelected = $this->getConditions();
                if (empty($conditionsSelected)) {
                    DebugService::logMessage('callback_controller.log', 'No conditions set in the configuration for CMS users');

                    return;
                } else {
                    if (in_array('acymailing', $tablesSelected)) {
                        $acymailingUserClass = new AcymailingUserClass();
                        $acymailingUserClass->handleBatchCallback($actionSelected, $conditionsSelected, $results, $allTests);
                    }

                    if (in_array('acymailing5', $tablesSelected)) {
                        $acymailing5UserClass = new Acymailing5UserClass();
                        $acymailing5UserClass->handleBatchCallback($actionSelected, $conditionsSelected, $results, $allTests);
                    }

                    if (in_array('cms', $tablesSelected)) {
                        $cmsUserClass = new CmsUserClass();
                        $cmsUserClass->handleBatchCallback($actionSelected, $conditionsSelected, $results, $allTests);
                    }
                }
            }
        }

        $i = 0;
        $tests = [];
        foreach($allTests as $oneResult){
            $tests[] = $oneResult;
            $i++;

            if ($i % 100 == 0) {
                $this->insertTestResult($tests);
                $tests = [];
            }
        }

        // Handle remaining tests
        if (!empty($tests)) {
            $this->insertTestResult($tests);
        }

        $this->sendNextBatch();
    }

    private function insertTestResult($tests)
    {
        $firstResult = array_shift($tests);
        $columns = array_keys($firstResult);

        $insertQuery = 'INSERT INTO #__acyc_test ('.implode(', ', $columns).') VALUES ';

        $insertQuery .= '('.implode(',', array_values($firstResult)).')';
        foreach ($tests as $values) {
            $insertQuery .= ',('.implode(',', array_values($values)).')';
        }

        foreach ($columns as $i => $oneColumn) {
            $columns[$i] = $oneColumn.' = VALUES('.$oneColumn.')';
        }
        $insertQuery .= ' ON DUPLICATE KEY UPDATE '.implode(', ', $columns);

        try {
            // We execute the query to store the email tested
            Database::query($insertQuery);
        } catch (\Exception $exception) {
            DebugService::logMessage('callback_controller.log', 'Error on updating the table test - '.$exception->getMessage());
        }
    }

    private function sendNextBatch()
    {
        $cronService = new CronService();
        $cronService->sendNextBatchToAPI();
    }
}
