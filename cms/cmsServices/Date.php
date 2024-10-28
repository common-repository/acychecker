<?php

namespace AcyCheckerCmsServices;


class Date
{
    public static function getTimeOffsetCMS()
    {
        static $timeoffset = null;
        if ($timeoffset === null) {
            $timeoffset = Database::getCMSConfig('offset');

            if (!is_numeric($timeoffset)) {
                $timezone = new \DateTimeZone($timeoffset);
                $timeoffset = $timezone->getOffset(new \DateTime());
            }
        }

        return $timeoffset;
    }

    public static function dateTimeCMS($time)
    {
        return date('Y-m-d H:i:s', $time);
    }
}
