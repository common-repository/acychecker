<?php


namespace AcyCheckerCmsServices;

use AcyChecker\Services\DebugService;
use \AcyChecker\Services\RouterService;

class WordPressMenu
{
    public function __construct()
    {
        add_action('wp_ajax_'.ACYC_COMPONENT.'_router', [$this, 'router']);
        if (defined('WP_ADMIN') && WP_ADMIN) {
            // Add AcyChecker menu in the back-end's left menu of WordPress
            add_action('admin_menu', [$this, 'addMenus'], 10);
        }
    }

    public function addMenus()
    {
        $svg = '<svg enable-background="new 0 0 400 400" version="1.1" viewBox="0 0 400 400" xml:space="preserve" xmlns="http://www.w3.org/2000/svg"><style type="text/css">.st0{fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;} .st1{fill:#FFFFFF;stroke:#000000;stroke-width:2;stroke-miterlimit:10;} .st2{fill:#FFFFFF;} .st3{opacity:0.5;fill:#FFFFFF;stroke:#000000;stroke-width:2;stroke-miterlimit:10;}</style><path class="st0" d="m240.52 197.86h-81.88c-7.08 0-12.82-5.74-12.82-12.82v-8.74c0-7.08 5.74-12.82 12.82-12.82h81.88c7.08 0 12.82 5.74 12.82 12.82v8.74c-0.01 7.08-5.75 12.82-12.82 12.82z"/><path class="st1" d="m119.72 79.61 153.37-7.89c2.84 0.3 10.72 1.49 17.98 7.77 7.01 6.07 9.38 13.36 10.13 16.14 2.47 18.66 4.93 37.32 7.4 55.97 0.05 1.82 0.05 9.32-5.58 15.87-4.74 5.53-10.66 7.11-12.67 7.55h-174.58c-3.25-0.53-10.34-2.16-16.96-7.88-4.59-3.97-7.12-8.34-8.44-11.1 1.15-18.74 2.3-37.48 3.45-56.22 0.26-1.61 1.82-10.33 9.97-16.02 6.82-4.75 13.93-4.37 15.93-4.19z"/><path class="st0" d="m140.99 123.38c6.45-1.42 13.42-2.71 20.88-3.74 9.28-1.28 17.97-1.98 25.94-2.29 8.26-0.48 16.73-0.85 25.39-1.11 9.71-0.29 19.18-0.41 28.39-0.4 1.92-0.01 6.15 0.21 10.61 2.79 5.21 3.02 7.58 7.43 8.39 9.13 1.31 5.27 2.61 10.54 3.92 15.82 0.07 0.58 0.47 4.27-2.24 7.38-2.86 3.28-6.84 3.26-7.39 3.24-6.64-1.08-13.75-1.99-21.3-2.61-12.37-1.03-23.8-1.13-34.09-0.71-8.56 0.03-17.46 0.25-26.68 0.71-11.59 0.58-22.66 1.48-33.16 2.61-3.26 0.36-6.38-0.9-8.11-3.37-1.46-2.08-1.44-4.32-1.38-5.16 0.95-4.53 1.9-9.05 2.85-13.58 0.54-1.25 1.44-2.94 2.93-4.66 1.82-2.08 3.74-3.34 5.05-4.05z"/><circle cx="165.6" cy="133.57" r="8.61"/><circle cx="232.65" cy="132.1" r="8.61"/><path d="m102.38 95.08c1.15-0.14 4.42-0.68 7.31-3.37 2.39-2.22 3.28-4.76 3.61-5.92-2.31-2.37-4.62-4.75-6.94-7.12-0.52-0.26-1.78-0.8-3.44-0.66-1.48 0.13-2.54 0.74-3.02 1.07-4.36-5.42-8.73-10.85-13.09-16.27 0.23-0.4 0.94-1.74 0.68-3.51-0.22-1.49-1.01-2.46-1.34-2.83-0.19-0.22-1.62-1.79-3.93-1.72-2.13 0.07-3.41 1.48-3.62 1.72-0.35 0.45-0.89 1.28-1.15 2.44-0.36 1.6 0.02 2.91 0.22 3.48 0.17 0.38 0.78 1.62 2.22 2.37 1.67 0.87 3.27 0.46 3.62 0.36 4.39 5.55 8.77 11.11 13.16 16.66-0.29 0.65-0.58 1.45-0.8 2.4-0.26 1.13-0.34 2.13-0.34 2.93 0.69 1.15 1.55 2.41 2.62 3.71 1.46 1.76 2.93 3.17 4.23 4.26z"/><path d="m282 79.06c0.4 1.09 1.68 4.15 4.96 6.34 2.71 1.81 5.39 2.1 6.59 2.16 1.78-2.8 3.56-5.59 5.34-8.39 0.14-0.57 0.37-1.92-0.15-3.5-0.47-1.41-1.31-2.3-1.73-2.7 4.28-5.49 8.55-10.99 12.83-16.48 0.44 0.13 1.91 0.52 3.58-0.14 1.4-0.56 2.17-1.55 2.45-1.95 0.17-0.23 1.37-1.99 0.77-4.22-0.55-2.06-2.22-2.98-2.51-3.13-0.52-0.23-1.45-0.57-2.64-0.56-1.64 0.02-2.83 0.68-3.33 1.01-0.33 0.26-1.4 1.13-1.79 2.71-0.46 1.83 0.3 3.28 0.48 3.61-4.4 5.54-8.8 11.09-13.2 16.63-0.7-0.13-1.55-0.23-2.51-0.23-1.16 0.01-2.15 0.16-2.93 0.34-0.96 0.94-1.99 2.06-3.01 3.4-1.41 1.83-2.44 3.58-3.2 5.1z"/><path class="st2" d="m206.82 356.37c-57.31 0.8-98.94-49.51-94.08-92.23 1.23-14.24 2.49-28.47 3.77-42.71 2-20 20.67-35.69 41.6-35.78 29.16-0.08 58.24-1.15 87.18-3.18 20.76-1.47 39.94 13.26 43.01 33.63 2.03 14.47 4.09 28.94 6.17 43.41 7.38 43.58-30.33 96.07-87.65 96.86z"/><path class="st0" d="m206.82 356.37c-57.31 0.8-98.94-49.51-94.08-92.23 1.23-14.24 2.49-28.47 3.77-42.71 2-20 20.67-35.69 41.6-35.78 29.16-0.08 58.24-1.15 87.18-3.18 20.76-1.47 39.94 13.26 43.01 33.63 2.03 14.47 4.09 28.94 6.17 43.41 7.38 43.58-30.33 96.07-87.65 96.86z"/><path class="st1" d="m295.21 246.52c-16.7-27.82-28.24-33.21-26.47-43.13 0.2-1.14 1.24-6.96 6.08-11.07 3.86-3.28 8.57-4.15 11.02-4.16 14.92-0.1 33.6 42.25 36.22 63.96 0.57 4.73 2.1 17.43-3.32 20.23-2.22 1.15-4.87 0.24-6.74-0.42-8.31-2.96-14.27-21.21-16.79-25.41z"/><path class="st3" d="m295.21 246.52c-16.7-27.82-28.24-33.21-26.47-43.13 0.2-1.14 1.24-6.96 6.08-11.07 3.86-3.28 8.57-4.15 11.02-4.16 14.92-0.1 33.6 42.25 36.22 63.96 0.57 4.73 2.1 17.43-3.32 20.23-2.22 1.15-4.87 0.24-6.74-0.42-8.31-2.96-14.27-21.21-16.79-25.41z"/><path class="st1" d="m107.91 253.32c16.7-27.82 28.24-33.21 26.47-43.13-0.2-1.14-1.24-6.96-6.08-11.07-3.86-3.28-8.57-4.15-11.02-4.16-14.92-0.1-33.6 42.25-36.22 63.96-0.57 4.73-2.1 17.43 3.32 20.23 2.22 1.15 4.87 0.24 6.74-0.42 8.31-2.96 14.28-21.22 16.79-25.41z"/><path class="st3" d="m107.91 253.32c16.7-27.82 28.24-33.21 26.47-43.13-0.2-1.14-1.24-6.96-6.08-11.07-3.86-3.28-8.57-4.15-11.02-4.16-14.92-0.1-33.6 42.25-36.22 63.96-0.57 4.73-2.1 17.43 3.32 20.23 2.22 1.15 4.87 0.24 6.74-0.42 8.31-2.96 14.28-21.22 16.79-25.41z"/><path class="st0" d="m177.82 236.68 28.06-15.83c0.27-0.09 0.7-0.2 1.22-0.2 0.51 0 0.93 0.11 1.2 0.21 4.32 2.17 8.64 4.35 12.96 6.52l-0.14 21.7-13.41-8.04-12.38 7.15 0.24 14.79 12.69 7.6 12.91-7.46 0.16 18.73-13.39 8.79c-0.14 0.1-0.62 0.43-1.31 0.39-0.6-0.03-1.01-0.33-1.15-0.44-9.19-5.11-18.37-10.22-27.56-15.33l-0.1-38.58z"/><path class="st0" d="m177.82 236.68c5.83 3.84 11.66 7.68 17.5 11.52 4.13-2.38 8.26-4.77 12.38-7.15l13.41 8.04c0.05-7.23 0.1-14.47 0.14-21.7-4.32-2.17-8.64-4.35-12.96-6.52-1.08-0.3-1.86-0.2-2.42-0.01-0.41 0.14-0.6 0.3-1.43 0.81-0.32 0.2-0.13 0.07-2.06 1.17-1.58 0.89-1.75 1-3.07 1.74-1.47 0.83-1.63 0.91-3.73 2.09-1.16 0.65-1.66 0.94-3.07 1.73-2.43 1.37-3.83 2.16-5.54 3.12-1.98 1.1-4.97 2.79-9.15 5.16z"/><path class="st0" d="m177.82 236.68c5.83 3.84 11.66 7.68 17.5 11.52l0.24 14.79c-5.8 4.04-11.6 8.08-17.39 12.13-0.12-12.82-0.23-25.63-0.35-38.44z"/><path class="st0" d="m178.17 275.11c9.1 5.16 18.2 10.33 27.3 15.49 0.14 0.11 0.37 0.27 0.7 0.36 0.34 0.1 0.62 0.08 0.74 0.07 0.22-0.02 0.37-0.06 0.41-0.08 0.13-0.04 0.23-0.09 0.3-0.12 0.03-0.02 0.12-0.06 0.24-0.13 0.08-0.05 0.14-0.09 0.16-0.11 0.18-0.13 0.45-0.3 1.2-0.79 0.01 0 0.18-0.12 0.54-0.35 0.51-0.34 0.91-0.6 1.4-0.92 1.49-0.98 2.75-1.81 4.67-3.07 0.92-0.6 1.02-0.66 2.77-1.81 1.15-0.76 2.1-1.38 2.73-1.79-0.05-6.24-0.11-12.49-0.16-18.73-4.3 2.49-8.61 4.97-12.91 7.46-4.23-2.53-8.46-5.07-12.69-7.6-5.81 4.04-11.6 8.08-17.4 12.12z"/></svg>';
        add_menu_page(
            __('Dashboard', 'acychecker'),
            'AcyChecker',
            'read',
            ACYC_COMPONENT.'_dashboard',
            [$this, 'router'],
            'data:image/svg+xml;base64,'.base64_encode(
                $svg
            ),
            42
        );

        add_submenu_page(
            ACYC_COMPONENT.'_dashboard',
            __('Clean my database', 'acychecker'),
            __('Clean my database', 'acychecker'),
            'read',
            ACYC_COMPONENT.'_database',
            [$this, 'router']
        );

        add_submenu_page(
            ACYC_COMPONENT.'_dashboard',
            __('Block on registration', 'acychecker'),
            __('Block on registration', 'acychecker'),
            'read',
            ACYC_COMPONENT.'_registration',
            [$this, 'router']
        );

        add_submenu_page(
            ACYC_COMPONENT.'_dashboard',
            __('Tests', 'acychecker'),
            __('Tests', 'acychecker'),
            'read',
            ACYC_COMPONENT.'_tests',
            [$this, 'router']
        );

        add_submenu_page(
            ACYC_COMPONENT.'_dashboard',
            __('Configuration', 'acychecker'),
            __('Configuration', 'acychecker'),
            'read',
            ACYC_COMPONENT.'_configuration',
            [$this, 'router']
        );

        // In WordPress, the first submenu is called "AcyChecker" instead of "Dashboard" so we rename it manually
        global $submenu;
        if (isset($submenu[ACYC_COMPONENT.'_dashboard'])) {
            $submenu[ACYC_COMPONENT.'_dashboard'][0][0] = __('Dashboard', 'acychecker');
        }
    }

    public function router()
    {
        new RouterService();
    }
}
