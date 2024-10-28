<?php


namespace AcyChecker\Classes;


use AcyChecker\Libraries\AcycClass;
use AcyCheckerCmsServices\Database;
use AcyCheckerCmsServices\Language;

class TestClass extends AcycClass
{
    const STEP = [
        'finished' => 1,
        'in_progress' => 0,
        'pending' => 2,
        'failed' => 3,
    ];
    const NO_STEP = 'all';

    public function __construct()
    {
        parent::__construct();

        $this->table = 'test';
        $this->pkey = 'email';
        $this->namekey = 'email';
    }

    public function getStatusLabels()
    {
        return [
            self::STEP['in_progress'] => __('In progress', 'acychecker'),
            self::STEP['finished'] => __('Finished', 'acychecker'),
            self::STEP['pending'] => __('Pending', 'acychecker'),
            self::STEP['failed'] => __('Failed', 'acychecker'),
        ];
    }

    public function getMatchingElements(&$settings)
    {
        $query = 'SELECT * FROM #__acyc_test AS test';
        $queryCount = 'SELECT COUNT(*) FROM #__acyc_test AS test';

        $where = $this->buildFiltersFromSettings($settings);
        $query .= $where;
        $queryCount .= $where;

        if (!empty($settings['ordering']) && !empty($settings['orderingSortOrder'])) {
            $query .= ' ORDER BY test.'.Database::secureDBColumn($settings['ordering']).' '.Database::secureDBColumn(strtoupper($settings['orderingSortOrder']));
        } else {
            $query .= ' ORDER BY test.date DESC';
        }

        $elements = Database::loadObjectList($query, 'email', $settings['offset'], $settings['per_page']);
        $total = Database::loadResult($queryCount);

        if (empty(count($elements)) && !empty($total)) {
            $settings['offset'] = 0;
            $settings['page'] = 1;
            $elements = Database::loadObjectList($query, 'email', $settings['offset'], $settings['per_page']);
        }

        return [
            'elements' => $elements,
            'total' => $total,
        ];
    }

    public function buildFiltersFromSettings($settings)
    {
        $filters = [];

        if (isset($settings['current_status']) && $settings['current_status'] !== self::NO_STEP && in_array($settings['current_status'], array_keys(self::STEP))) {
            $filters[] = 'test.current_step = '.intval(self::STEP[$settings['current_status']]);
        }

        if (!empty($settings['search'])) {
            $search = Database::escapeDB('%'.$settings['search'].'%');
            $filters[] = 'test.email LIKE '.$search.' OR test.test_result LIKE '.$search.' OR test.raw_result LIKE '.$search;
        }

        return empty($filters) ? '' : ' WHERE ('.implode(') AND (', $filters).')';
    }

    public function getStepNumber($status, $settings)
    {
        $query = 'SELECT COUNT(*) FROM #__acyc_test AS test';

        $settings['current_status'] = $status;

        $query .= $this->buildFiltersFromSettings($settings);

        return Database::loadResult($query);
    }

    public function getTotalDisposable()
    {
        return Database::loadResult('SELECT COUNT(*) FROM #__acyc_test WHERE disposable = 1');
    }

    public function getTotalFree()
    {
        return Database::loadResult('SELECT COUNT(*) FROM #__acyc_test WHERE free = 1');
    }

    public function getTotalAcceptAll()
    {
        return Database::loadResult('SELECT COUNT(*) FROM #__acyc_test WHERE accept_all = 1');
    }

    public function getTotalRoleEmail()
    {
        return Database::loadResult('SELECT COUNT(*) FROM #__acyc_test WHERE role_email = 1');
    }

    public function getBlockedUsersRepartition()
    {
        $date = date('Y-m-d', strtotime(date('Y-m-1')));

        return Database::loadObjectList(
            'SELECT block_reason, COUNT(block_reason) AS value 
            FROM #__acyc_test 
            WHERE block_reason IS NOT NULL 
                AND `date` > '.Database::escapeDB($date).' 
            GROUP BY block_reason'
        );
    }

    public function getNumberOfTestByDay()
    {
        $date = date('Y-m-d', strtotime(date('Y-m-1')));

        return Database::loadObjectList(
            'SELECT COUNT(*) AS count, DATE_FORMAT(`date`, "%Y-%m-%d") AS date_formated 
            FROM #__acyc_test 
            WHERE `date` > '.Database::escapeDB($date).' 
            GROUP BY date_formated',
            'date_formated'
        );
    }

    public function deleteAllTested()
    {
        return Database::query('DELETE FROM #__acyc_test WHERE current_step = '.intval(self::STEP['finished']));
    }

    public function getNextBatch($limit = 5000)
    {
        return Database::loadResultArray(
            'SELECT email 
            FROM #__acyc_test 
            WHERE current_step = '.intval(self::STEP['pending']).' 
                AND batch_id IS NULL
            ORDER BY date ASC 
            LIMIT '.intval($limit)
        );
    }

    public function getNbResults($step = null)
    {
        $query = 'SELECT COUNT(*) FROM #__acyc_test';
        if (!is_null($step)) {
            $query .= ' WHERE current_step = '.intval($step);
        }

        return Database::loadResult($query);
    }

    public function setBatchId($batchId, $emails)
    {
        if (empty($batchId) || empty($emails)) return;

        $emails = array_map('AcyCheckerCmsServices\Database::escapeDB', $emails);

        Database::query(
            'UPDATE #__acyc_test 
            SET batch_id = '.intval($batchId).', current_step = '.intval(self::STEP['in_progress']).' 
            WHERE email IN ('.implode(',', $emails).')'
        );
    }

    public function setBatchAsFailed($batchId)
    {
        if (empty($batchId)) return;

        Database::query('UPDATE #__acyc_test SET current_step = '.intval(self::STEP['failed']).' WHERE batch_id = '.intval($batchId));
    }

    public function deletePending()
    {
        return Database::query('DELETE FROM #__acyc_test WHERE current_step = '.intval(self::STEP['pending']));
    }

    public function setBlockReason($emails, $reason)
    {
        if (empty($emails)) return;

        $emails = array_map('AcyCheckerCmsServices\Database::escapeDB', $emails);

        $reason = empty($reason) ? 'NULL' : Database::escapeDB($reason);
        Database::query('UPDATE #__acyc_test SET `block_reason` = '.$reason.' WHERE `email` IN ('.implode(', ', $emails).')');
    }

    public function getTestResults($start, $limit)
    {
        return Database::loadArrayList(
            'SELECT `email`, `disposable`, `free`, `accept_all`, `role_email` AS `role`, `domain_exists` AS `d_exists`, `test_result` AS `exists` 
            FROM #__acyc_test 
            WHERE `current_step` = '.intval(self::STEP['finished']).' 
            LIMIT '.intval($start).','.intval($limit)
        );
    }

    public function getResultsByEmail($emails)
    {
        if (empty($emails)) return [];
        $emails = array_map('AcyCheckerCmsServices\Database::escapeDB', $emails);

        return Database::loadArrayList(
            'SELECT `email`, `disposable`, `free`, `accept_all`, `role_email` AS `role`, `domain_exists` AS `d_exists`, `test_result` AS `exists` 
            FROM #__acyc_test 
            WHERE `current_step` = '.intval(self::STEP['finished']).' 
                AND `email` IN ('.implode(', ', $emails).')'
        );
    }

    public function addBlockReason($bulkReasons)
    {
        $reasonMap = [];
        foreach ($bulkReasons as $email => $reason) {
            $reasonMap[$reason['block_reason']][] = Database::escapeDB($email);
        }

        foreach ($reasonMap as $reason => $emails) {
            // $reason is already escaped
            Database::query('UPDATE #__acyc_test SET `block_reason` = '.$reason.' WHERE `email` IN ('.implode(', ', $emails).')');
        }
    }

    public function getNbMatchingUsers($conditions, $selectedUsers = [])
    {
        if (empty($conditions)) {
            return 0;
        }

        $query = 'SELECT COUNT(*) FROM #__acyc_test WHERE ';

        $where = [];
        foreach ($conditions as $oneCondition) {
            if (in_array($oneCondition, ['disposable', 'accept_all'])) {
                $where[] = $oneCondition.' = 1';
            } elseif ($oneCondition === 'free_domain') {
                $where[] = 'free = 1';
            } elseif ($oneCondition === 'role_based') {
                $where[] = 'role_email = 1';
            } elseif ($oneCondition === 'domain_not_exists') {
                $where[] = 'domain_exists = 0';
            } elseif ($oneCondition === 'invalid_smtp') {
                $where[] = 'test_result = "not_existing"';
            }
        }

        if (!empty($selectedUsers)) {
            $selectedUsers = array_map('AcyCheckerCmsServices\Database::escapeDB', $selectedUsers);
            $query .= 'email IN ('.implode(', ', $selectedUsers).') AND ';
        }

        return Database::loadResult($query.' ('.implode(' OR ', $where).')');
    }
}
