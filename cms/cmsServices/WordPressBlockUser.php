<?php


namespace AcyCheckerCmsServices;


use AcyChecker\Services\DebugService;

class WordPressBlockUser
{
    public function __construct()
    {
        add_filter('authenticate', [$this, 'verifyUser'], 1000, 3);
    }

    private function isUserBlocked($userId)
    {
        $userMeta = get_userdata($userId);

        return empty($userMeta->roles);
    }

    public function verifyUser($user, $username, $password)
    {
        if (is_a($user, 'WP_User') && $this->isUserBlocked($user->ID)) {
            return new \WP_Error('authentication_failed', __('Could not authenticate, your account has been blocked', 'acychecker'));
        }

        return $user;
    }
}
