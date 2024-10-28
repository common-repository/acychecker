<?php


namespace AcyChecker\Controllers;

use AcyChecker\Libraries\AcycController;
use AcyChecker\Services\DebugService;
use AcyChecker\Services\TestService;
use AcyCheckerCmsServices\Security;

class CallbackController extends AcycController
{
    public function handleCallback()
    {
        $testId = Security::getVar('int', 'test_id', 0);
        if (empty($testId)) {
            DebugService::logMessage('callback_controller.log', 'Param test_id is empty');

            exit;
        }

        $result = Security::getVar('string', 'result', '');
        if (empty($result)) {
            DebugService::logMessage('callback_controller.log', 'Param result is empty');

            exit;
        }

        $testService = new TestService();
        $this->config->load();

        $configUrls = $this->config->get('urls_results_batch', '[]');
        $configUrls = json_decode($configUrls, true);

        $decodedResult = $testService->decodeResult($result);

        if ($decodedResult === null) {
            DebugService::logMessage('callback_controller.log', 'Error decoding the received data for '.$testId);

            // We received the results but couldn't decode them, this means that the content may have been truncated by the server in $_POST
            // We can let the client's server call our API to get the results instead, which has less risk of having this issue
            if (!empty($configUrls[$testId])) {
                $configUrls[$testId]['last_check'] = 0;
                $this->config->save(['urls_results_batch' => json_encode($configUrls)]);
            }

            exit;
        }

        if (!empty($configUrls[$testId])) {
            unset($configUrls[$testId]);
            $this->config->save(['urls_results_batch' => json_encode($configUrls)]);
        }

        $testService->handleBatchResults($testId, $decodedResult);
        exit;
    }
}
