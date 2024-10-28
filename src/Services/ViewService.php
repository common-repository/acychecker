<?php

namespace AcyChecker\Services;


use AcyCheckerCmsServices\Security;

class ViewService
{
    public static function getView($ctrl, $view)
    {
        return ACYC_VIEW.$ctrl.DS.$view.'.php';
    }
}
