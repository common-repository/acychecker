<?php


namespace AcyChecker\Classes;


use AcyChecker\Libraries\AcycClass;
use AcyCheckerCmsServices\Database;

class AcymailingListClass extends AcycClass
{
    public function getAllListsForSelect()
    {
        $lists = Database::loadObjectList('SELECT name, id FROM #__acym_list WHERE type = "standard"');

        if (empty($lists)) return [];

        $return = [];

        foreach ($lists as $list) {
            $return[$list->id] = $list->name;
        }

        return $return;
    }
}
