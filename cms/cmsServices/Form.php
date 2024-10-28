<?php

namespace AcyCheckerCmsServices;


class Form
{
    public static function formToken()
    {
        return '<input type="hidden" name="_wpnonce" value="'.wp_create_nonce('acymnonce').'">';
    }

    /**
     * Check token with all the possibilities
     */
    public static function checkToken()
    {
        $token = Security::getVar('cmd', '_wpnonce');
        if (!wp_verify_nonce($token, 'acymnonce')) {
            die('Invalid Token');
        }
    }

    public static function getFormToken()
    {
        $token = Security::getVar('cmd', '_wpnonce', '');
        if (empty($token)) {
            $token = wp_create_nonce('acymnonce');
        }
        Security::setVar('_wpnonce', $token);

        return '_wpnonce='.$token;
    }

    public static function noTemplate($component = true)
    {
        return 'noheader=1';
    }

    public static function isNoTemplate()
    {
        return Security::getVar('cmd', 'noheader') == '1';
    }

    public static function setNoTemplate($status = true)
    {
        if ($status) {
            Security::setVar('noheader', '1');
        } else {
            unset($_REQUEST['noheader']);
        }
    }


    /**
     * @param bool   $token
     * @param string $task
     * @param string $currentStep
     * @param string $currentCtrl
     * @param bool   $addPage
     */
    public static function formOptions($token = true, $task = '', $currentStep = null, $currentCtrl = '', $addPage = true)
    {
        if (!empty($currentStep)) {
            echo '<input type="hidden" name="step" value="'.$currentStep.'"/>';
        }
        echo '<input type="hidden" name="nextstep" value=""/>';
        echo '<input type="hidden" name="task" value="'.$task.'"/>';
        if ($addPage) {
            echo '<input type="hidden" name="page" value="'.Security::getVar('cmd', 'page', '').'"/>';
        }
        echo '<input type="hidden" name="ctrl" value="'.(empty($currentCtrl) ? Security::getVar('cmd', 'ctrl', '') : $currentCtrl).'"/>';
        if ($token) {
            echo Form::formToken();
        }
        echo '<button type="submit" class="is-hidden" id="formSubmit"></button>';
    }

    public static function includeHeaders()
    {
        do_action('head');
    }
}
