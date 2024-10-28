<?php


namespace AcyChecker\Services;


use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;
use AcyCheckerCmsServices\Url;
use AcyCheckerCmsServices\Router as CmsRouter;

class RouterService
{
    public function __construct()
    {
        $ctrl = Security::getVar('cmd', 'ctrl');
        $task = Security::getVar('cmd', 'task');

        if (empty($ctrl)) {
            $ctrl = str_replace(ACYC_COMPONENT.'_', '', Security::getVar('cmd', 'page'));

            if (empty($ctrl)) {
                $ctrl = 'dashboard';
            }

            Security::setVar('ctrl', $ctrl);
        }

        $controllerNamespace = 'AcyChecker\\Controllers\\'.ucfirst($ctrl).'Controller';

        if (!class_exists($controllerNamespace)) {
            Security::raiseError(E_ERROR, 404, __('Page not found', 'acychecker').': '.$ctrl);

            return;
        }

        $controller = new $controllerNamespace;

        if (empty($controller)) {
            CmsRouter::redirect(Url::completeLink('dashboard', false, true));

            return;
        }

        if (empty($task)) {
            $task = Security::getVar('cmd', 'defaulttask', $controller->defaultTask);
            Security::setVar('task', $task);
        }

        // In case the API failed to send the results to the callback url, we check for the results after a day
        $routineService = new RoutineService();
        $routineService->checkLostResults();

        $controller->call($task);
    }
}
