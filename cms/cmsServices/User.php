<?php

namespace AcyCheckerCmsServices;


class User
{
    public static function getCmsUserDbStructure()
    {
        $acymCmsUserVars = new \stdClass();
        $acymCmsUserVars->table = '#__users';
        $acymCmsUserVars->name = 'display_name';
        $acymCmsUserVars->username = 'user_login';
        $acymCmsUserVars->id = 'ID';
        $acymCmsUserVars->email = 'user_email';
        $acymCmsUserVars->registered = 'user_registered';
        $acymCmsUserVars->blocked = 'user_status';

        return $acymCmsUserVars;
    }

    public static function getGroupsByUser($userid = null, $recursive = null, $names = false)
    {
        if ($userid === null) {
            $user = wp_get_current_user();
        } else {
            $user = new \WP_User($userid);
        }

        return $user->roles;
    }

    public static function getGroups()
    {
        $usersPerGroup = Database::loadObjectList('SELECT meta_value, COUNT(meta_value) AS nbusers FROM #__usermeta WHERE meta_key = "#__capabilities" GROUP BY meta_value');

        $nbUsers = [];
        foreach ($usersPerGroup as $oneGroup) {
            $oneGroup->meta_value = unserialize($oneGroup->meta_value);
            $nbUsers[key($oneGroup->meta_value)] = $oneGroup->nbusers;
        }

        $roles = wp_roles();
        if (empty($roles->roles)) {
            $groups = Database::loadResult('SELECT option_value FROM #__options WHERE option_name = "#__user_roles"');
            $groups = unserialize($groups);
        } else {
            $groups = $roles->roles;
        }

        foreach ($groups as $key => $group) {
            $newGroup = new \stdClass();
            $newGroup->id = $key;
            $newGroup->value = $key;
            $newGroup->parent_id = 0;
            $newGroup->text = translate_user_role($group['name']);
            $newGroup->nbusers = empty($nbUsers[$key]) ? 0 : $nbUsers[$key];
            $groups[$key] = $newGroup;
        }

        return $groups;
    }

    public static function currentUserId()
    {
        return get_current_user_id();
    }

    public static function currentUserName($userid = null)
    {
        if (!empty($userid)) {
            $special = get_user_by('id', $userid);

            return $special->display_name;
        }

        $current_user = wp_get_current_user();

        return $current_user->display_name;
    }

    public static function currentUserEmail($userid = null)
    {
        if (!empty($userid)) {
            $special = get_user_by('id', $userid);

            return $special->user_email;
        }

        $current_user = wp_get_current_user();

        return $current_user->user_email;
    }

    public static function getCmsUserEdit($userId)
    {
        return 'user-edit.php?user_id='.intval($userId);
    }
}
