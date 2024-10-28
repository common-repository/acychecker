<?php

namespace AcyCheckerCmsServices;


use AcyChecker\Classes\ConfigurationClass;
use AcyChecker\Services\ApiService;
use AcyChecker\Services\DebugService;

class WordPressRegistration
{
    public function __construct()
    {
        // Hooks when a new WP user is about to be created
        add_filter('user_profile_update_errors', [$this, 'blockNewUsers'], 10, 3);
        add_filter('registration_errors', [$this, 'blockNewUsersRegistration'], 10, 3);
        // Events manager
        add_filter('em_registration_errors', [$this, 'blockNewUsersRegistration'], 10, 3);
        // WooCommerce
        add_filter('woocommerce_registration_errors', [$this, 'blockNewUsersRegistration'], 10, 3);
    }

    public function blockNewUsersRegistration($errors, $login, $email)
    {
        $user = new \stdClass();
        $user->user_email = $email;
        $this->blockNewUsers($errors, email_exists($email), $user);

        return $errors;
    }

    public function blockNewUsers(&$errors, $update, $user)
    {
        if ($update) return;

        $config = new ConfigurationClass();
        $integrations = explode(',', $config->get('registration_integrations'));

        // The email verification is disabled in the configuration
        if (!in_array('cms', $integrations)) return;

        $conditions = $config->get('registration_conditions');
        if (empty($conditions)) return;

        // Perform test using ACYC code API
        $apiService = new ApiService();
        $emailOk = $apiService->testEmail($user->user_email, $conditions);
        if ($emailOk !== true) {
            $errors->add('acyc_register_error', __('Invalid email address', 'acychecker'));
        }
    }
}
