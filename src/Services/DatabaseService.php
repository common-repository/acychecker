<?php

namespace AcyChecker\Services;

use AcyChecker\Libraries\AcycObject;
use AcyCheckerCmsServices\Extension;
use AcyCheckerCmsServices\Language;

class DatabaseService extends AcycObject
{
    public static function getTablesForSelect()
    {
        $tablesSelect = [
            [
                'value' => 'cms',
                'text' => sprintf(__('%s users', 'acychecker'), ACYC_CMS_TITLE),
            ],
        ];

        if (Extension::isExtensionActive('joomla' == ACYC_CMS ? ACYC_ACYMAILING_COMPONENT : ACYC_ACYMAILING_COMPONENT.'/index.php')) {
            $tablesSelect[] = [
                'value' => 'acymailing',
                'text' => sprintf(__('%s users', 'acychecker'), 'AcyMailing'),
            ];
        }

        if (Extension::isExtensionActive('joomla' == ACYC_CMS ? ACYC_ACYMAILING5_COMPONENT : ACYC_ACYMAILING5_COMPONENT.'/index.php')) {
            $tablesSelect[] = [
                'value' => 'acymailing5',
                'text' => sprintf(__('%s users', 'acychecker'), 'AcyMailing 5'),
            ];
        }

        return $tablesSelect;
    }

    public static function getConditionsForSelect()
    {
        return [
            [
                'value' => 'disposable',
                'text' => __('Is a disposable email address', 'acychecker'),
                'description' => __('This test check if the email address of the user is a temporary one or not, for example email addresses created on yopmail', 'acychecker'),
            ],
            [
                'value' => 'accept_all',
                'text' => __('Is an accept all email address', 'acychecker'),
                'description' => __('Accept all means that no matter which email address you have on this domain it will be redirected to a default inbox of the SMTP server', 'acychecker'),
            ],
            [
                'value' => 'free_domain',
                'text' => __('Is registered through a free domain', 'acychecker'),
                'description' => __('This means that the user is using a free mail provider like Gmail, Yahoo or outlook', 'acychecker'),
            ],
            [
                'value' => 'role_based',
                'text' => __('Is a role based email address', 'acychecker'),
                'description' => __('This means that email address is assigned to a team and not a person for example sales@acyba.com', 'acychecker'),
            ],
            [
                'value' => 'domain_not_exists',
                'text' => __('Is registered through a non-existent domain', 'acychecker'),
                'description' => __('This means that the domain does not exist', 'acychecker'),
            ],
            [
                'value' => 'invalid_smtp',
                'text' => __('Does not exist on the SMTP server of the domain', 'acychecker'),
                'description' => __('This means that we ask to the SMTP server of the subscriber if the email address exist', 'acychecker'),
            ],
        ];
    }

    public static function getActionsForSelect(bool $isCleanDatabaseMenuContext = true): array
    {
        $actionSelect = [
            [
                'value' => 'do_nothing',
                'text' => __('Decide later', 'acychecker'),
            ],
            [
                'value' => 'block_users',
                'text' => __('Block users', 'acychecker'),
            ],
            [
                'value' => 'delete_users',
                'text' => __('Delete users', 'acychecker'),
            ],
        ];

        if (!$isCleanDatabaseMenuContext) {
            array_shift($actionSelect);
        } else {
            array_pop($actionSelect);
        }

        return $actionSelect;
    }
}
