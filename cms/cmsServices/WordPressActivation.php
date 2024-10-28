<?php

namespace AcyCheckerCmsServices;

use AcyChecker\Classes\ConfigurationClass;
use AcyChecker\Services\UpdateService;

class WordPressActivation
{
    public function __construct()
    {
        $entryPoint = ACYC_FOLDER.'acychecker.php';
        // Install the DB and sample data on first activation (not installation because of FTP install)
        register_activation_hook($entryPoint, [$this, 'install']);
        add_action('plugins_loaded', [$this, 'updateDB']);
    }

    public function updateDB()
    {
        if (!file_exists(ACYC_FOLDER.'update.php')) return;
        $this->install();
    }

    public function install()
    {
        $file_name = ACYC_FOLDER.'tables.sql';
        $handle = fopen($file_name, 'r');
        $queries = fread($handle, filesize($file_name));
        fclose($handle);

        if (is_multisite()) {
            $currentBlog = get_current_blog_id();
            $sites = function_exists('get_sites') ? get_sites() : wp_get_sites();

            foreach ($sites as $site) {
                if (is_object($site)) {
                    $site = get_object_vars($site);
                }
                switch_to_blog($site['blog_id']);
                $this->sampleData($queries);
            }

            switch_to_blog($currentBlog);
        } else {
            $this->sampleData($queries);
        }

        if (file_exists(ACYC_FOLDER.'update.php')) {
            unlink(ACYC_FOLDER.'update.php');
        }
    }

    private function sampleData($queries)
    {
        global $wpdb;
        $prefix = Database::getPrefix();

        $tables = str_replace('#__', $prefix, $queries);
        $tables = explode('CREATE TABLE IF NOT EXISTS', $tables);

        foreach ($tables as $oneTable) {
            $oneTable = trim($oneTable);
            if (empty($oneTable)) {
                continue;
            }
            $wpdb->query('CREATE TABLE IF NOT EXISTS'.$oneTable);
        }

        $this->update();
    }

    public function update()
    {
        $configurationClass = new ConfigurationClass();
        if (!file_exists(ACYC_FOLDER.'update.php') && $configurationClass->get('installcomplete', 0) != 0) {
            return;
        }

        $updateService = new UpdateService();
        $updateService->addPref();
        $updateService->updatePref();
        $updateService->updateSQL();

        $newConfig = new \stdClass();
        $newConfig->installcomplete = 1;

        $configurationClass->save($newConfig);
    }
}
