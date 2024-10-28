<?php

namespace AcyChecker\Controllers;


use AcyChecker\Libraries\AcycController;
use AcyChecker\Services\ApiService;
use AcyChecker\Services\DebugService;
use AcyChecker\Services\SecurityService;
use AcyCheckerCmsServices\Ajax;
use AcyCheckerCmsServices\Database;
use AcyCheckerCmsServices\File;
use AcyCheckerCmsServices\Form;
use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Message;
use AcyCheckerCmsServices\Router;
use AcyCheckerCmsServices\Security;
use AcyCheckerCmsServices\Url;

class ConfigurationController extends AcycController
{
    private $messagesNoHtml = [];

    public function __construct()
    {
        parent::__construct();

        $this->name = 'Configuration';
    }

    public function defaultTask()
    {
        $this->layout = 'default';

        $data = [
            'licenseKey' => $this->config->get('license_key'),
            'blacklist' => $this->config->get('blacklist'),
            'whitelist' => $this->config->get('whitelist'),
        ];

        $this->breadcrumb[__('Configuration', 'acychecker')] = Url::completeLink('configuration');

        $this->display($data);
    }

    public function save()
    {
        Form::checkToken();

        $formData = Security::getVar('array', 'config', []);
        if (empty($formData)) {
            Router::redirect(Url::completeLink('configuration', false, true));
        }

        $licenseKeyBeforeSave = $this->config->get('license_key');
        $isLicenseKeyUpdated = isset($formData['license_key']) && $licenseKeyBeforeSave !== $formData['license_key'];

        $status = $this->config->save($formData);

        if ($status) {
            Message::enqueueMessage(__('Successfully saved', 'acychecker'));

            if ($isLicenseKeyUpdated) {
                // If we add a key or edit it, we try to attach it
                if (!empty($formData['license_key'])) {
                    $this->attachLicenseKey();
                } else {
                    // If we remove a key, we unlink it
                    $this->detachLicenseKey();
                }
            }
        } else {
            Message::enqueueMessage(__('Error saving', 'acychecker'), 'error');
        }

        Router::redirect(Url::completeLink('configuration', false, true));
    }

    public function ajaxCheckDB()
    {
        // Get the structure that the AcyChecker tables should have in the database
        $correctTablesStructure = $this->getCorrectTablesStructure();
        // Get the current structure of the AcyChecker tables and tries to repair/create them if needed
        $currentTablesStructure = $this->getCurrentTablesStructure($correctTablesStructure);
        // Adds missing columns in AcyChecker tables and missing indexes / primary keys / constraints on the tables
        $this->fixCurrentStructure($correctTablesStructure, $currentTablesStructure);

        $result = '';
        if (empty($this->messagesNoHtml)) {
            $result = '<i class="acycicon-check-circle acyc__color__green"></i>';
        } else {
            $nbMessages = count($this->messagesNoHtml);
            foreach ($this->messagesNoHtml as $i => $oneMsg) {
                $result .= '<span style="color:'.$oneMsg['color'].'">'.$oneMsg['msg'].'</span>';
                if ($i < $nbMessages) {
                    $result .= '<br />';
                }
            }
        }

        Ajax::sendAjaxResponse('', ['html' => $result]);
    }

    /**
     * Returns the structure that the AcyChecker tables should have in the database
     *
     * @return array
     */
    private function getCorrectTablesStructure(): array
    {
        $correctTablesStructure = [
            'structure' => [],
            'createTable' => [],
            'indexes' => [],
            'constraints' => [],
        ];

        $queries = file_get_contents(ACYC_BACK.'tables.sql');
        $tables = explode('CREATE TABLE IF NOT EXISTS ', $queries);

        // For each table, get its name, its column names and its indexes / primary key
        foreach ($tables as $oneTable) {
            if (strpos($oneTable, '`#__') !== 0) {
                continue;
            }

            $tableName = substr($oneTable, 1, strpos($oneTable, '`', 1) - 1);
            $correctTablesStructure['createTable'][$tableName] = 'CREATE TABLE IF NOT EXISTS '.$oneTable;
            $correctTablesStructure['indexes'][$tableName] = [];
            $correctTablesStructure['constraints'][$tableName] = [];

            $fields = explode("\n", $oneTable);
            foreach ($fields as $key => $oneField) {
                if (strpos($oneField, '#__') === 1) {
                    continue;
                }
                $oneField = rtrim(trim($oneField), ',');

                // Find the column names and remember them
                if (substr($oneField, 0, 1) === '`') {
                    $columnName = substr($oneField, 1, strpos($oneField, '`', 1) - 1);
                    $correctTablesStructure['structure'][$tableName][$columnName] = trim($oneField, ',');
                    continue;
                }

                // Remember the primary key and indexes of the table
                if (strpos($oneField, 'PRIMARY KEY') === 0) {
                    $correctTablesStructure['indexes'][$tableName]['PRIMARY'] = $oneField;
                } elseif (strpos($oneField, 'INDEX') === 0) {
                    $firstBackquotePos = strpos($oneField, '`');
                    $indexName = substr($oneField, $firstBackquotePos + 1, strpos($oneField, '`', $firstBackquotePos + 1) - $firstBackquotePos - 1);

                    $correctTablesStructure['indexes'][$tableName][$indexName] = $oneField;
                } elseif (strpos($oneField, 'FOREIGN KEY') !== false) {
                    preg_match('/(#__fk.*)\`/Uis', $fields[$key - 1], $matchesConstraints);
                    preg_match('/(#__.*)\`\(`(.*)`\)/Uis', $fields[$key + 1], $matchesTable);
                    preg_match('/\`(.*)\`/Uis', $oneField, $matchesColumn);
                    if (!empty($matchesConstraints) && !empty($matchesTable) && !empty($matchesColumn)) {
                        $correctTablesStructure['constraints'][$tableName][$matchesConstraints[1]] = [
                            'table' => $matchesTable[1],
                            'column' => $matchesColumn[1],
                            'table_column' => $matchesTable[2],
                        ];
                    }
                }
            }
        }

        $correctTablesStructure['tableNames'] = array_keys($correctTablesStructure['structure']);

        return $correctTablesStructure;
    }

    /**
     * Returns the current structure of the AcyChecker tables and tries to repair/create them if needed
     *
     * @param array $correctTablesStructure
     *
     * @return array
     */
    private function getCurrentTablesStructure(array $correctTablesStructure): array
    {
        $currentTablesStructure = [];
        $existingTables = Database::getTableList();

        foreach ($correctTablesStructure['tableNames'] as $oneTableName) {
            $tableNameWithPrefix = str_replace('#__', Database::getPrefix(), $oneTableName);
            if (in_array($tableNameWithPrefix, $existingTables)) {
                try {
                    $columns = Database::loadObjectList('SHOW COLUMNS FROM '.$oneTableName);
                } catch (\Exception $e) {
                    $columns = null;
                }
            } else {
                $columns = null;
            }

            if (!empty($columns)) {
                foreach ($columns as $oneField) {
                    $currentTablesStructure[$oneTableName][$oneField->Field] = $oneField->Field;
                }
                continue;
            }

            // We didn't get the columns, the table crashed or doesn't exist
            $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(Database::getDBError()), 0, 200));
            $this->messagesNoHtml[] = [
                'error' => false,
                'color' => 'blue',
                'msg' => sprintf(__('Could not load columns from the table %1$s : %2$s', 'acychecker'), $oneTableName, $errorMessage),
            ];

            if (strpos($errorMessage, 'marked as crashed')) {
                try {
                    $isError = Database::query('REPAIR TABLE '.$oneTableName);
                } catch (\Exception $e) {
                    $isError = null;
                }

                if ($isError === null) {
                    $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(Database::getDBError()), 0, 200));
                    $this->messagesNoHtml[] = [
                        'error' => true,
                        'color' => 'red',
                        'msg' => sprintf(__('[ERROR]Could not repair the table %1$s : %2$s', 'acychecker'), $oneTableName, $errorMessage),
                    ];
                } else {
                    $this->messagesNoHtml[] = [
                        'error' => false,
                        'color' => 'green',
                        'msg' => sprintf(__('[OK]Problem solved: Table %s repaired', 'acychecker'), $oneTableName),
                    ];
                }
                continue;
            } else {
                try {
                    // Create missing table
                    $isError = Database::query($correctTablesStructure['createTable'][$oneTableName]);
                } catch (\Exception $e) {
                    $isError = null;
                }

                if ($isError === null) {
                    $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(Database::getDBError()), 0, 200));
                    $this->messagesNoHtml[] = [
                        'error' => true,
                        'color' => 'red',
                        'msg' => sprintf(__('[ERROR]Could not create the table %1$s : %2$s', 'acychecker'), $oneTableName, $errorMessage),
                    ];
                } else {
                    $this->messagesNoHtml[] = [
                        'error' => false,
                        'color' => 'green',
                        'msg' => sprintf(__('[OK]Problem solved: Table %s created', 'acychecker'), $oneTableName),
                    ];
                }
            }
        }

        return $currentTablesStructure;
    }

    /**
     * Adds missing columns in AcyChecker tables and missing indexes / primary keys on the tables
     *
     * @param array $correctTablesStructure
     * @param array $currentTablesStructure
     *
     * @return void
     */
    private function fixCurrentStructure(array $correctTablesStructure, array $currentTablesStructure)
    {
        foreach ($correctTablesStructure['tableNames'] as $oneTableName) {
            if (empty($currentTablesStructure[$oneTableName])) {
                continue;
            }

            $this->addMissingColumns($correctTablesStructure['structure'][$oneTableName], $currentTablesStructure[$oneTableName], $oneTableName);
            $this->removeExtraColumns($correctTablesStructure['structure'][$oneTableName], $currentTablesStructure[$oneTableName], $oneTableName);
            $this->fixDefaultValues($correctTablesStructure['structure'][$oneTableName], $oneTableName);
            $this->addMissingTableKeys($correctTablesStructure['indexes'][$oneTableName], $oneTableName);
            $this->addMissingTableConstraints($correctTablesStructure['constraints'][$oneTableName], $oneTableName);
        }
    }

    /**
     * Add missing columns in an AcyChecker table
     *
     * @param array  $correctTableColumns
     * @param array  $currentTableColumnNames
     * @param string $oneTableName
     *
     * @return void
     */
    private function addMissingColumns(array $correctTableColumns, array $currentTableColumnNames, string $oneTableName)
    {
        $idealColumnNames = array_keys($correctTableColumns);
        $missingColumns = array_diff($idealColumnNames, $currentTableColumnNames);

        if (empty($missingColumns)) {
            return;
        }

        foreach ($missingColumns as $oneColumn) {
            $this->messagesNoHtml[] = [
                'error' => false,
                'color' => 'blue',
                'msg' => sprintf(__('Column %1$s missing in %2$s', 'acychecker'), $oneColumn, $oneTableName),
            ];

            try {
                $isError = Database::query('ALTER TABLE '.$oneTableName.' ADD '.$correctTableColumns[$oneColumn]);
            } catch (\Exception $e) {
                $isError = null;
            }

            if ($isError === null) {
                $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(Database::getDBError()), 0, 200));
                $this->messagesNoHtml[] = [
                    'error' => true,
                    'color' => 'red',
                    'msg' => sprintf(__('[ERROR]Could not add the column %1$s on the table %2$s : %3$s', 'acychecker'), $oneColumn, $oneTableName, $errorMessage),
                ];
            } else {
                $this->messagesNoHtml[] = [
                    'error' => false,
                    'color' => 'green',
                    'msg' => sprintf(__('[OK]Problem solved: Added %1$s in %2$s', 'acychecker'), $oneColumn, $oneTableName),
                ];
            }
        }
    }

    private function removeExtraColumns(array $correctTableColumns, array $currentTableColumnNames, string $oneTableName)
    {
        $idealColumnNames = array_keys($correctTableColumns);
        $extraColumns = array_diff($currentTableColumnNames, $idealColumnNames);

        if (empty($extraColumns)) {
            return;
        }

        foreach ($extraColumns as $oneColumn) {
            $this->messagesNoHtml[] = [
                'error' => false,
                'color' => 'blue',
                'msg' => sprintf(__('Extra column %1$s detected in table %2$s', 'acychecker'), $oneColumn, $oneTableName),
            ];

            try {
                $isError = Database::query('ALTER TABLE '.$oneTableName.' DROP COLUMN `'.Database::secureDBColumn($oneColumn).'`');
            } catch (\Exception $e) {
                $isError = null;
            }

            if ($isError === null) {
                $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(Database::getDBError()), 0, 200));
                $this->messagesNoHtml[] = [
                    'error' => true,
                    'color' => 'red',
                    'msg' => sprintf(__('[ERROR]Could not remove the column %1$s from the table %2$s: %3$s', 'acychecker'), $oneColumn, $oneTableName, $errorMessage),
                ];
            } else {
                $this->messagesNoHtml[] = [
                    'error' => false,
                    'color' => 'green',
                    'msg' => sprintf(__('[OK]Problem solved: Removed column %1$s from table %2$s', 'acychecker'), $oneColumn, $oneTableName),
                ];
            }
        }
    }

    private function fixDefaultValues($correctTableColumns, $oneTableName)
    {
        try {
            $currentTableColumns = Database::loadObjectList(
                'SELECT COLUMN_NAME, COLUMN_DEFAULT, IS_NULLABLE, COLUMN_TYPE 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '.Database::escapeDB($oneTableName),
                'COLUMN_NAME'
            );

            if (empty($currentTableColumns)) {
                //TODO
                return;
            }
        } catch (\Exception $e) {
            $this->messagesNoHtml[] = [
                'error' => true,
                'color' => 'orange',
                'msg' => sprintf(__('[ERROR]Could not get current columns for table %1$s to check for default value: %2$s', 'acychecker'), $oneTableName, $e->getMessage()),
            ];

            return;
        }

        foreach ($correctTableColumns as $oneColumn => $oneColumnDefinition) {
            $defaultValue = '';
            if (preg_match('#DEFAULT ([^ ]+)$#Ui', $oneColumnDefinition, $matches)) {
                $defaultValue = $matches[1];
            }

            if (strlen($defaultValue) === 0) {
                continue;
            }

            // if current value is surrounded by double quotes, replace them by quotes before comparing
            if (!empty($currentTableColumns[$oneColumn]->COLUMN_DEFAULT) && substr($currentTableColumns[$oneColumn]->COLUMN_DEFAULT, 0, 1) === '"') {
                $currentTableColumns[$oneColumn]->COLUMN_DEFAULT = '\''.substr($currentTableColumns[$oneColumn]->COLUMN_DEFAULT, 1, -1).'\'';
            }

            if (!empty($currentTableColumns[$oneColumn]->COLUMN_DEFAULT) && $currentTableColumns[$oneColumn]->COLUMN_DEFAULT === $defaultValue) {
                continue;
            }

            $this->messagesNoHtml[] = [
                'error' => false,
                'color' => 'blue',
                'msg' => sprintf(__('The default value for the column %1$s in the table %2$s is not correct', 'acychecker'), $oneColumn, $oneTableName),
            ];

            try {
                $isError = Database::query('ALTER TABLE '.$oneTableName.' CHANGE `'.Database::secureDBColumn($oneColumn).'` '.$oneColumnDefinition);
            } catch (\Exception $e) {
                $isError = null;
            }

            if ($isError === null) {
                $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(Database::getDBError()), 0, 200));
                $this->messagesNoHtml[] = [
                    'error' => true,
                    'color' => 'red',
                    'msg' => sprintf(__('[ERROR]Could not update the default value for the column %1$s in the table %2$s: %3$s', 'acychecker'), $oneColumn, $oneTableName, $errorMessage),
                ];
            } else {
                $this->messagesNoHtml[] = [
                    'error' => false,
                    'color' => 'green',
                    'msg' => sprintf(__('[OK]Problem solved: Updated the default value for the column %1$s in the table %2$s', 'acychecker'), $oneColumn, $oneTableName),
                ];
            }
        }
    }

    /**
     * Adds the missing indexes / primary keys on an AcyChecker table
     *
     * @param array  $correctTableIndexes
     * @param string $oneTableName
     *
     * @return void
     */
    private function addMissingTableKeys(array $correctTableIndexes, string $oneTableName)
    {
        // Add missing index and primary keys
        $results = Database::loadObjectList('SHOW INDEX FROM '.$oneTableName, 'Key_name');
        if (empty($results)) {
            $results = [];
        }

        foreach ($correctTableIndexes as $name => $query) {
            $name = Database::prepareQuery($name);
            if (in_array($name, array_keys($results))) {
                continue;
            }

            // The index / primary key is missing, add it

            $keyName = $name === 'PRIMARY' ? 'primary key' : 'index '.$name;

            $this->messagesNoHtml[] = [
                'error' => false,
                'color' => 'blue',
                'msg' => sprintf(__('%1$s missing in %2$s', 'acychecker'), $keyName, $oneTableName),
            ];

            try {
                $isError = Database::query('ALTER TABLE '.$oneTableName.' ADD '.$query);
            } catch (\Exception $e) {
                $isError = null;
            }

            if ($isError === null) {
                $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(Database::getDBError()), 0, 200));
                $this->messagesNoHtml[] = [
                    'error' => true,
                    'color' => 'red',
                    'msg' => sprintf(__('[ERROR]Could not add the %1$s on the table %2$s : %3$s', 'acychecker'), $keyName, $oneTableName, $errorMessage),
                ];
            } else {
                $this->messagesNoHtml[] = [
                    'error' => false,
                    'color' => 'green',
                    'msg' => sprintf(__('[OK]Problem solved: Added %1$s to %2$s', 'acychecker'), $keyName, $oneTableName),
                ];
            }
        }
    }

    /**
     * Adds or fixes the table's foreign keys
     *
     * @param array  $correctTableConstraints
     * @param string $oneTableName
     *
     * @return void
     */
    private function addMissingTableConstraints(array $correctTableConstraints, string $oneTableName)
    {
        if (empty($correctTableConstraints)) {
            return;
        }

        $tableNameQuery = str_replace('#__', Database::getPrefix(), $oneTableName);
        $databaseName = Database::loadResult('SELECT DATABASE();');
        $foreignKeys = Database::loadObjectList(
            'SELECT i.TABLE_NAME, i.CONSTRAINT_TYPE, i.CONSTRAINT_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME, k.COLUMN_NAME
            FROM information_schema.TABLE_CONSTRAINTS AS i 
            LEFT JOIN information_schema.KEY_COLUMN_USAGE AS k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME 
            WHERE i.TABLE_NAME = '.Database::escapeDB($tableNameQuery).' AND i.CONSTRAINT_TYPE = "FOREIGN KEY" AND i.TABLE_SCHEMA = '.Database::escapeDB($databaseName),
            'CONSTRAINT_NAME'
        );

        Database::query('SET foreign_key_checks = 0');

        foreach ($correctTableConstraints as $constraintName => $constraintInfo) {
            $constraintTableNamePrefix = str_replace('#__', Database::getPrefix(), $constraintInfo['table']);
            $constraintName = str_replace('#__', Database::getPrefix(), $constraintName);

            if (!empty($foreignKeys[$constraintName]) && $foreignKeys[$constraintName]->REFERENCED_TABLE_NAME === $constraintTableNamePrefix && $foreignKeys[$constraintName]->REFERENCED_COLUMN_NAME === $constraintInfo['table_column'] && $foreignKeys[$constraintName]->COLUMN_NAME === $constraintInfo['column']) {
                continue;
            }

            $this->messagesNoHtml[] = [
                'error' => false,
                'color' => 'blue',
                'msg' => sprintf(__('Foreign key %1$s not well set for table %2$s', 'acychecker'), $constraintName, $oneTableName),
            ];

            // The foreign key exists, but it is incorrect. We remove it then add the correct one
            if (!empty($foreignKeys[$constraintName])) {
                try {
                    $isError = Database::query('ALTER TABLE `'.$oneTableName.'` DROP FOREIGN KEY `'.$constraintName.'`');
                } catch (\Exception $e) {
                    $isError = null;
                }

                if ($isError === null) {
                    $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(Database::getDBError()), 0, 200));
                    $this->messagesNoHtml[] = [
                        'error' => true,
                        'color' => 'red',
                        'msg' => sprintf(__('[ERROR]Could not add the foreign key %1$s on the table %2$s : %3$s', 'acychecker'), $constraintName, $oneTableName, $errorMessage),
                    ];
                    continue;
                }
            }

            // Add the missing foreign key
            try {
                $isError = Database::query(
                    'ALTER TABLE `'.$oneTableName.'` ADD CONSTRAINT `'.$constraintName.'` FOREIGN KEY (`'.$constraintInfo['column'].'`) REFERENCES `'.$constraintInfo['table'].'` (`'.$constraintInfo['table_column'].'`) ON DELETE NO ACTION ON UPDATE NO ACTION;'
                );
            } catch (\Exception $e) {
                $isError = null;
            }

            if ($isError === null) {
                $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(Database::getDBError()), 0, 200));
                $this->messagesNoHtml[] = [
                    'error' => true,
                    'color' => 'red',
                    'msg' => sprintf(__('[ERROR]Could not add the foreign key %1$s on the table %2$s : %3$s', 'acychecker'), $constraintName, $oneTableName, $errorMessage),
                ];
            } else {
                $this->messagesNoHtml[] = [
                    'error' => false,
                    'color' => 'green',
                    'msg' => sprintf(__('[OK]Problem solved: Added foreign key %1$s to table %2$s', 'acychecker'), $constraintName, $oneTableName),
                ];
            }
        }

        Database::query('SET foreign_key_checks = 1');
    }

    public function seeLogs()
    {
        SecurityService::noCache();

        $type = Security::getVar('cmd', 'type');
        $types = [
            'batch' => 'batch_tests',
            'callback' => 'callback_controller',
            'individual' => 'individual_tests',
        ];

        if (!in_array($type, array_keys($types))) {
            exit;
        }

        $reportPath = DebugService::getLogPath($types[$type].'.log');

        if (file_exists($reportPath)) {
            try {
                $lines = 5000;
                $f = fopen($reportPath, 'rb');
                fseek($f, -1, SEEK_END);
                if (fread($f, 1) != "\n") {
                    $lines -= 1;
                }

                $report = '';
                while (ftell($f) > 0 && $lines >= 0) {
                    $seek = min(ftell($f), 4096); // Figure out how far back we should jump
                    fseek($f, -$seek, SEEK_CUR);
                    $report = ($chunk = fread($f, $seek)).$report; // Get the line
                    fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
                    $lines -= substr_count($chunk, "\n"); // Move to previous line
                }

                while ($lines++ < 0) {
                    $report = substr($report, strpos($report, "\n") + 1);
                }
                fclose($f);
            } catch (\Exception $e) {
                $report = '';
            }
        }

        if (empty($report)) {
            $report = __('The log file is empty', 'acychecker');
        }

        echo nl2br($report);
        exit;
    }

    public function deleteLogs()
    {
        $type = Security::getVar('cmd', 'type');
        $types = [
            'batch' => 'batch_tests',
            'callback' => 'callback_controller',
            'individual' => 'individual_tests',
        ];

        if (!in_array($type, array_keys($types))) {
            exit;
        }

        $reportPath = DebugService::getLogPath($types[$type].'.log');
        if (file_exists($reportPath)) {
            unlink($reportPath);
            Message::enqueueMessage(__('Logs deleted', 'acychecker'));
        }

        Router::redirect(Url::completeLink('configuration', false, true));
    }

    public function attachLicenseKey()
    {
        $formData = Security::getVar('array', 'config', []);
        $licenseKey = $formData['license_key'];
        $this->config->save(['license_key' => $licenseKey]);

        if (empty($licenseKey)) {
            Message::enqueueMessage(__('Please set a valid license key', 'acychecker'), 'error');
        } else {
            $apiService = new ApiService();
            $result = $apiService->getCredits();
            if (empty($result['success'])) {
                Message::enqueueMessage($result['message'], 'error');
            }
        }

        if (!empty($result['success'])) {
            $newConfig = [
                'license_key' => $licenseKey,
                'credits_used_batch' => $result['data']['credits_used_batch'],
                'credits_used_simple' => $result['data']['remaining_credits_simple'],
                'remaining_credits_batch' => $result['data']['remaining_credits_batch'],
                'remaining_credits_simple' => $result['data']['remaining_credits_simple'],
                'license_level' => $result['data']['license_level'],
                'license_last_check' => time(),
                'license_end_date' => $result['data']['end_date'],
            ];
        } else {
            $newConfig = [
                'license_key' => '',
                'credits_used_batch' => 0,
                'remaining_credits_batch' => 0,
                'credits_used_simple' => 0,
                'remaining_credits_simple' => 0,
            ];
        }

        $this->config->save($newConfig);

        Router::redirect(Url::completeLink('configuration', false, true));
    }

    public function detachLicenseKey()
    {
        $newConfig = [
            'license_key' => '',
            'credits_used_batch' => 0,
            'remaining_credits_batch' => 0,
            'credits_used_simple' => 0,
            'remaining_credits_simple' => 0,
            'license_level' => '',
            'license_last_check' => time(),
            'license_end_date' => '',
        ];

        $this->config->save($newConfig);

        Router::redirect(Url::completeLink('configuration', false, true));
    }
}
