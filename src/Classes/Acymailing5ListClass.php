<?php

namespace AcyChecker\Classes;

use AcyChecker\Libraries\AcycClass;
use AcyCheckerCmsServices\Database;

class Acymailing5ListClass extends AcycClass
{
    public function getAllListsForSelect()
    {
        $lists = Database::loadObjectList('SELECT name, listid FROM #__acymailing_list WHERE type = "list"');

        if (empty($lists)) return [];

        $return = [];

        foreach ($lists as $list) {
            $return[$list->listid] = $list->name;
        }

        return $return;
    }
}
