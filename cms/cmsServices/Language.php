<?php

namespace AcyCheckerCmsServices;


class Language
{
    public static function getWpLangCodes()
    {
        return [
            'af' => 'af-ZA',
            'ar' => 'ar-AA',
            'as' => 'as-AS', // Not sure
            'az' => 'az-AZ', // Not sure
            'bo' => 'bo-BO', // Not sure
            'ca' => 'ca-ES',
            'cy' => 'cy-GB',
            'el' => 'el-GR',
            'eo' => 'eo-XX',
            'et' => 'et-EE',
            'eu' => 'eu-ES',
            'fi' => 'fi-FI',
            'gd' => 'gd-GD', // Not sure
            'gu' => 'gu-GU', // Not sure
            'hr' => 'hr-HR',
            'hy' => 'hy-AM',
            'ja' => 'ja-JP',
            'kk' => 'kk-KK', // Not sure
            'km' => 'km-KH',
            'lo' => 'lo-LO', // Not sure
            'lv' => 'lv-LV',
            'mn' => 'mn-MN', // Not sure
            'mr' => 'mr-MR', // Not sure
            'ps' => 'ps-PS', // Not sure
            'sq' => 'sq-AL',
            'te' => 'te-TE',
            'th' => 'th-TH',
            'tl' => 'tl-TL', // Not sure
            'uk' => 'uk-UA',
            'ur' => 'ur-PK', // Not sure
            'vi' => 'vi-VN',
        ];
    }

    public static function translation($key)
    {
        $translation = __($key);

        if (strpos($translation, '\\') !== false) {
            $translation = str_replace(['\\\\', '\t', '\n'], ["\\", "\t", "\n"], $translation);
        }

        return $translation;
    }

    public static function translationSprintf()
    {
        $args = func_get_args();
        $args[0] = Language::translation($args[0]);

        return call_user_func_array('sprintf', $args);
    }

    public static function getLanguageTag($simple = false)
    {
        if (Security::isAdmin()) {
            $currentLocale = get_user_locale(User::currentUserId());
        } else {
            $currentLocale = get_locale();
        }

        $currentLocale = Language::convertWPLocaleToAcyLocale($currentLocale);

        global $acycLanguages;
        if (!isset($acycLanguages['currentLanguage'])) {
            $acycLanguages['currentLanguage'] = $currentLocale;
        }

        return $simple ? substr($acycLanguages['currentLanguage'], 0, 2) : $acycLanguages['currentLanguage'];
    }

    public static function loadLanguageFile($extension, $basePath = null, $lang = null, $reload = false, $default = true)
    {
    }

    public static function getLanguagePath($basePath, $language = null)
    {
        return rtrim(ACYC_LANGUAGE, DS);
    }

    public static function convertWPLocaleToAcyLocale($locale)
    {
        if (strpos($locale, '-') !== false) return $locale;

        $acycWPLangCodes = Language::getWpLangCodes();
        if (!empty($acycWPLangCodes[$locale])) return $acycWPLangCodes[$locale];

        if (strpos($locale, '_') === false) {
            return $locale.'-'.strtoupper($locale);
        } else {
            return str_replace('_', '-', $locale);
        }
    }

    public static function getLanguages($installed = false)
    {
        $acycWPLangCodes = Language::getWpLangCodes();

        $result = [];

        require_once ABSPATH.'wp-admin/includes/translation-install.php';
        $wplanguages = wp_get_available_translations();
        $languages = get_available_languages();
        foreach ($languages as $oneLang) {
            $wpLangCode = $oneLang;
            if (!empty($acycWPLangCodes[$oneLang])) $oneLang = $acycWPLangCodes[$oneLang];
            $langTag = str_replace('_', '-', $oneLang);

            $lang = new \stdClass();
            $lang->sef = empty($wplanguages[$oneLang]['iso'][1]) ? null : $wplanguages[$oneLang]['iso'][1];
            $lang->language = strtolower($langTag);
            $lang->name = empty($wplanguages[$wpLangCode]) ? $langTag : $wplanguages[$wpLangCode]['native_name'];
            $lang->exists = file_exists(ACYC_LANGUAGE.ACYC_COMPONENT.'-'.$oneLang.'.mo');
            $lang->content = true;

            $result[$langTag] = $lang;
        }

        if (!in_array('en-US', array_keys($result))) {
            $lang = new \stdClass();
            $lang->sef = 'en';
            $lang->language = 'en-us';
            $lang->name = 'English (United States)';
            $lang->exists = true;
            $lang->content = true;

            $result['en-US'] = $lang;
        }

        return $result;
    }
}
