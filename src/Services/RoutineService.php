<?php

namespace AcyChecker\Services;

use AcyChecker\Classes\TestClass;
use AcyChecker\Libraries\AcycObject;

class RoutineService extends AcycObject
{
    const MAX_ATTEMPTS = 5;
    const CHECK_DELAY = 21600;

    public function checkLostResults()
    {
        $urlsBatch = $this->getCurrentBatches();

        if (empty($urlsBatch)) {
            $cronService = new CronService();
            $cronService->sendNextBatchToAPI(false);

            return;
        }

        $time = time();
        $apiService = new ApiService();
        $testService = new TestService();
        $testClass = new TestClass();

        foreach ($urlsBatch as $batchId => $oneBatch) {
            if ($time - $oneBatch['last_check'] < self::CHECK_DELAY || $oneBatch['attempts'] > self::MAX_ATTEMPTS) continue;
            if ($oneBatch['attempts'] == self::MAX_ATTEMPTS) {
                $testClass->setBatchAsFailed($batchId);
                $urlsBatch[$batchId]['attempts']++;
                continue;
            }

            $urlsBatch[$batchId]['attempts']++;
            $urlsBatch[$batchId]['last_check'] = time();

            $result = $apiService->getBatchResult($batchId);
            if (empty($result)) continue;

            unset($urlsBatch[$batchId]);

            // handleBatchResults changes urls_results_batch, which is replaced when we save in this function so we save, call then reload
            $this->config->save(['urls_results_batch' => json_encode($urlsBatch)]);
            $testService->handleBatchResults($batchId, $result);
            $urlsBatch = $this->getCurrentBatches();
        }

        $this->config->save(['urls_results_batch' => json_encode($urlsBatch)]);
    }

    private function getCurrentBatches()
    {
        $this->config->load();
        $urlsBatch = $this->config->get('urls_results_batch', '[]');

        return json_decode($urlsBatch, true);
    }
}
