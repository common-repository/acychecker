<?php

namespace AcyChecker\Libraries;


use AcyCheckerCmsServices\Database;

class AcycClass extends AcycObject
{
    public $table = '';

    // Name of the Primary Key
    public $pkey = '';

    // Name of the namekey field (for non numeric values)
    public $namekey = '';

    // Handle errors
    public $errors = [];

    // Information messages, mainly for the cron report
    public $messages = [];

    // Column used to humanely identify the element
    public $nameColumn = 'name';

    public function getOneById($id)
    {
        return Database::loadObject(
            'SELECT * 
            FROM #__acyc_'.Database::secureDBColumn($this->table).' 
            WHERE `'.Database::secureDBColumn($this->pkey).'` = '.Database::escapeDB($id)
        );
    }

    public function getAll($key = null)
    {
        if (empty($key)) $key = $this->pkey;

        return Database::loadObjectList('SELECT * FROM #__acyc_'.Database::secureDBColumn($this->table), $key);
    }

    public function save($element)
    {
        $tableColumns = Database::getColumns($this->table);
        // We clone the element because we don't want to modify it for later in the code
        $cloneElement = clone $element;
        foreach ($cloneElement as $column => $value) {
            if (!in_array($column, $tableColumns)) {
                // Unset variables that don't exist in the table
                unset($cloneElement->$column);
                continue;
            }
            Database::secureDBColumn($column);
        }

        $pkey = $this->pkey;

        try {
            if (empty($cloneElement->$pkey) || empty($this->getOneById($cloneElement->$pkey))) {
                $status = Database::insertObject('#__acyc_'.$this->table, $cloneElement);
            } else {
                $status = Database::updateObject('#__acyc_'.$this->table, $cloneElement, $pkey);
            }

            if (!$status) {
                $dbError = strip_tags(Database::getDBError());
                if (!empty($dbError)) {
                    if (strlen($dbError) > 203) $dbError = substr($dbError, 0, 200).'...';
                    $this->errors[] = $dbError;
                }

                return false;
            }
        } catch (\Exception $e) {
            $dbError = $e->getMessage();
            if (strlen($dbError) > 203) $dbError = substr($dbError, 0, 200).'...';
            $this->errors[] = $dbError;

            return false;
        }

        //We return the element not modify if we want it later in the code
        return empty($cloneElement->$pkey) ? $status : $cloneElement->$pkey;
    }

    public function delete($elements)
    {
        if (empty($elements)) return 0;
        if (!is_array($elements)) $elements = [$elements];

        $column = is_numeric(reset($elements)) ? $this->pkey : $this->namekey;

        //Secure the query
        $escapedElements = [];
        foreach ($elements as $key => $val) {
            $escapedElements[$key] = Database::escapeDB($val);
        }

        if (empty($column) || empty($this->pkey) || empty($this->table) || empty($escapedElements)) {
            return false;
        }

        $query = 'DELETE FROM #__acyc_'.Database::secureDBColumn($this->table).' WHERE '.Database::secureDBColumn($column).' IN ('.implode(',', $escapedElements).')';
        $result = Database::query($query);

        if (!$result) return false;

        return $result;
    }
}
