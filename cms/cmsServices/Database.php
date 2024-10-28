<?php

namespace AcyCheckerCmsServices;


class Database
{
    public static function escapeDB($value)
    {
        // esc_sql replaces % by something like {svzzvzevzv} but it's normal, it will be replaced back by % before the query is executed
        return "'".esc_sql($value)."'";
    }

    public static function query($query)
    {
        global $wpdb;
        $query = Database::prepareQuery($query);

        $result = $wpdb->query($query);

        return $result === false ? null : $result;
    }

    public static function loadObjectList($query, $key = '', $offset = null, $limit = null)
    {
        global $wpdb;
        $query = Database::prepareQuery($query);

        if (isset($offset)) {
            $query .= ' LIMIT '.intval($offset).','.intval($limit);
        }

        $results = $wpdb->get_results($query);
        if (empty($key)) {
            return $results;
        }

        $sorted = [];
        foreach ($results as $oneRes) {
            $sorted[$oneRes->$key] = $oneRes;
        }

        return $sorted;
    }

    public static function loadArrayList($query, $key = '', $offset = null, $limit = null)
    {
        global $wpdb;
        $query = Database::prepareQuery($query);

        if (isset($offset)) {
            $query .= ' LIMIT '.intval($offset).','.intval($limit);
        }

        $results = $wpdb->get_results($query, ARRAY_A);
        if (empty($key)) {
            return $results;
        }

        $sorted = [];
        foreach ($results as $oneRes) {
            $sorted[$oneRes[$key]] = $oneRes;
        }

        return $sorted;
    }

    public static function prepareQuery($query)
    {
        global $wpdb;
        $query = str_replace('#__', $wpdb->prefix, $query);
        if (is_multisite()) {
            $query = str_replace($wpdb->prefix.'users', $wpdb->base_prefix.'users', $query);
            $query = str_replace($wpdb->prefix.'usermeta', $wpdb->base_prefix.'usermeta', $query);
        }

        return $query;
    }

    public static function addLimit(&$query, $limit = 1, $offset = null)
    {
        if (strpos($query, 'LIMIT ') !== false) return;

        $query .= ' LIMIT ';
        if (!empty($offset)) $query .= intval($offset).',';
        $query .= intval($limit);
    }

    public static function loadObject($query)
    {
        Database::addLimit($query);

        global $wpdb;
        $query = Database::prepareQuery($query);

        return $wpdb->get_row($query);
    }

    public static function loadResult($query)
    {
        global $wpdb;
        $query = Database::prepareQuery($query);

        return $wpdb->get_var($query);
    }

    public static function loadResultArray($query)
    {
        global $wpdb;
        $query = Database::prepareQuery($query);

        return $wpdb->get_col($query);
    }

    public static function getDBError()
    {
        global $wpdb;

        return $wpdb->last_error;
    }

    public static function insertObject($table, $element)
    {
        global $wpdb;
        $element = get_object_vars($element);
        $table = Database::prepareQuery($table);
        $wpdb->insert($table, $element);

        return $wpdb->insert_id;
    }

    public static function updateObject($table, $element, $pkey)
    {
        global $wpdb;
        $element = get_object_vars($element);
        $table = Database::prepareQuery($table);

        if (!is_array($pkey)) {
            $pkey = [$pkey];
        }

        $where = [];
        foreach ($pkey as $onePkey) {
            $where[$onePkey] = $element[$onePkey];
        }

        $nbUpdated = $wpdb->update($table, $element, $where);

        return $nbUpdated !== false;
    }

    public static function getPrefix()
    {
        global $wpdb;

        return $wpdb->prefix;
    }

    public static function getTableList()
    {
        global $wpdb;

        return Database::loadResultArray("SELECT table_name FROM information_schema.tables WHERE table_schema = '".$wpdb->dbname."' AND table_name LIKE '".$wpdb->prefix."%'");
    }

    public static function getCMSConfig($varname, $default = null)
    {
        $map = [
            'offset' => 'timezone_string',
            'list_limit' => 'posts_per_page',
            'sitename' => 'blogname',
            'mailfrom' => 'new_admin_email',
            'feed_email' => 'new_admin_email',
        ];

        if (!empty($map[$varname])) {
            $varname = $map[$varname];
        }
        $value = get_option($varname, $default);

        // In WP there are multiple possible formats in the same option for the timezone
        if ($varname == 'timezone_string' && empty($value)) {
            $value = Database::getCMSConfig('gmt_offset');

            if (empty($value)) {
                $value = 'UTC';
            } elseif ($value < 0) {
                $value = 'GMT'.$value;
            } else {
                $value = 'GMT+'.$value;
            }
        }

        // In WP this could be any number, but Acy pagination only works with 5,10,15,20,25,30,50 or 100
        if ($varname == 'posts_per_page') {
            $possibilities = [5, 10, 15, 20, 25, 30, 50, 100];
            $closest = 5;
            foreach ($possibilities as $possibility) {
                if (abs($value - $closest) > abs($value - $possibility)) {
                    $closest = $possibility;
                }
            }
            $value = $closest;
        }

        return $value;
    }

    public static function secureDBColumn($fieldName)
    {
        if (!is_string($fieldName) || preg_match('|[^a-z0-9#_.-]|i', $fieldName) !== 0) {
            die('field, table or database "'.Security::escape($fieldName).'" not secured');
        }

        return $fieldName;
    }

    public static function getColumns($table, $acyTable = true, $addPrefix = true)
    {
        if ($addPrefix) {
            $prefix = $acyTable ? '#__acyc_' : '#__';
            $table = $prefix.$table;
        }

        return Database::loadResultArray('SHOW COLUMNS FROM '.Database::secureDBColumn($table));
    }
}
