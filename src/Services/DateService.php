<?php

namespace AcyChecker\Services;

use AcyChecker\Libraries\AcycObject;
use AcyCheckerCmsServices\Database;
use AcyCheckerCmsServices\Date;
use AcyCheckerCmsServices\Language;

class DateService extends AcycObject
{
    public static function getDate($time = 0, $format = '%d %B %Y %H:%M')
    {
        if (empty($time)) return '';

        if (is_numeric($format)) {
            $format = __('l, j F Y', 'acychecker'.$format);
        }

        $format = str_replace(
            ['%A', '%d', '%B', '%m', '%Y', '%y', '%H', '%M', '%S', '%a', '%I', '%p', '%w'],
            ['l', 'd', 'F', 'm', 'Y', 'y', 'H', 'i', 's', 'D', 'h', 'a', 'w'],
            $format
        );

        //Not sure why but sometimes it fails... so lets try to catch the error...
        try {
            return DateService::date($time, $format, false);
        } catch (\Exception $e) {
            return date($format, $time);
        }
    }

    public static function date($time = 'now', $format = null, $useTz = true, $translate = true)
    {
        if ($time == 'now') {
            $time = time();
        }

        if (is_numeric($time)) {
            $time = Date::dateTimeCMS((int)$time);
        }

        if (empty($format)) {
            $format = __('l, j F Y', 'acychecker');
        }

        //Don't use timezone
        if ($useTz === false) {
            $date = new \DateTime($time);

            if ($translate) {
                return DateService::translateDate($date->format($format));
            } else {
                return $date->format($format);
            }
        } else {
            //use timezone
            $cmsOffset = Database::getCMSConfig('offset');

            $timezone = new \DateTimeZone($cmsOffset);

            if (!is_numeric($cmsOffset)) {
                $cmsOffset = $timezone->getOffset(new \DateTime);
            }

            if ($translate) {
                return DateService::translateDate(date($format, strtotime($time) + $cmsOffset));
            } else {
                return date($format, strtotime($time) + $cmsOffset);
            }
        }
    }

    public static function translateDate($date)
    {
        $map = [
            'January' => __('January', 'acychecker'),
            'February' => __('February', 'acychecker'),
            'March' => __('March', 'acychecker'),
            'April' => __('April', 'acychecker'),
            'May' => __('May', 'acychecker'),
            'June' => __('June', 'acychecker'),
            'July' => __('July', 'acychecker'),
            'August' => __('August', 'acychecker'),
            'September' => __('September', 'acychecker'),
            'October' => __('October', 'acychecker'),
            'November' => __('November', 'acychecker'),
            'December' => __('December', 'acychecker'),
            'Monday' => __('Monday', 'acychecker'),
            'Tuesday' => __('Tuesday', 'acychecker'),
            'Wednesday' => __('Wednesday', 'acychecker'),
            'Thursday' => __('Thursday', 'acychecker'),
            'Friday' => __('Friday', 'acychecker'),
            'Saturday' => __('Saturday', 'acychecker'),
            'Sunday' => __('Sunday', 'acychecker'),
        ];

        foreach ($map as $english => $translation) {
            if ($translation === $english) {
                continue;
            }

            $date = preg_replace('#'.preg_quote($english).'( |,|$)#i', $translation.'$1', $date);
            $date = preg_replace('#'.preg_quote(substr($english, 0, 3)).'( |,|$)#i', mb_substr($translation, 0, 3).'$1', $date);
        }

        return $date;
    }
}
