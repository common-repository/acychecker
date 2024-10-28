<?php

namespace AcyChecker\Classes;

use AcyChecker\Libraries\AcycClass;
use AcyCheckerCmsServices\Database;
use AcyCheckerCmsServices\Message;

class ConfigurationClass extends AcycClass
{
    var $table = 'configuration';
    var $pkey = 'name';
    var $values = [];

    public function load()
    {
        $this->values = Database::loadObjectList('SELECT * FROM #__acyc_configuration', 'name');
    }

    public function get($namekey, $default = '')
    {
        if (empty($this->values) && !empty($this->config->values)) {
            $this->values = $this->config->values;
        }

        if (isset($this->values[$namekey])) {
            return $this->values[$namekey]->value;
        }

        return $default;
    }

    public function save($newConfig, $escape = true)
    {
        // We do a replace so that values are always kept up to date and added if necessary in the mean time
        $query = 'REPLACE INTO #__acyc_configuration (`name`, `value`) VALUES ';

        $params = [];
        foreach ($newConfig as $name => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            //We update the current instance in the same time
            if (empty($this->values[$name])) {
                $this->values[$name] = new \stdClass();
            }
            $this->values[$name]->value = $value;

            // We do a strip tags to avoid HTML injections
            if ($escape) {
                $params[] = '('.Database::escapeDB(strip_tags($name)).','.Database::escapeDB(strip_tags($value)).')';
            } else {
                $params[] = '('.Database::escapeDB($name).','.Database::escapeDB($value).')';
            }
        }

        if (empty($params)) return true;

        $query .= implode(',', $params);

        try {
            $status = Database::query($query);
        } catch (\Exception $e) {
            $status = false;
        }
        if ($status === false) {
            Message::display(isset($e) ? $e->getMessage() : substr(strip_tags(Database::getDBError()), 0, 200).'...', 'error');
        }

        return $status;
    }
}
