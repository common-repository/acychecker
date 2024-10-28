<?php


namespace AcyCheckerCmsServices;

use AcyChecker\Controllers\CallbackController;
use AcyChecker\Services\CronService;

class WordPressCron
{
    public function __construct()
    {
        add_action('init', [$this, 'processCron']);
        add_action('wp_loaded', [$this, 'handleCallback']);
    }

    public function processCron()
    {
        if (Form::isNoTemplate()) return;

        $cronService = new CronService();
        $cronService->process();
    }

    public function handleCallback()
    {
        $page = Security::getVar('string', 'page', '');
        $isPost = !empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
        if (!is_admin() && $isPost && $page === 'acychecker_front') {
            $callbackController = new CallbackController();
            $callbackController->handleCallback();
        }
    }
}
