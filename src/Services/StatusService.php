<?php


namespace AcyChecker\Services;


use AcyCheckerCmsServices\Language;

class StatusService
{
    const NO_COLOR = 0;
    const RED_FOR_YES = 1;
    const RED_FOR_NO = 2;

    public static function initStatusListing($status, $current)
    {
        $statusHtml = '';

        if (empty($status)) return $statusHtml;

        $statusHtml = '<div class="cell grid-x acyc__listing__status__container"><input name="status" type="hidden" id="acyc__listing__status" value="'.$current.'">';

        $i = 0;
        foreach ($status as $value => $oneStatus) {
            $class = $value == $current ? 'acyc__listing__status__selected' : '';
            if ($i != 0) $statusHtml .= '<span class="cell shrink acyc__listing__status__separator">|</span>';
            $statusHtml .= '<a href="#" class="acyc__listing__status__one cell shrink '.$class.'" data-acyc-status="'.$value.'">'.$oneStatus['text'].' ('.$oneStatus['number'].')</a>';
            $i++;
        }

        $statusHtml .= '</div>';

        return $statusHtml;
    }

    public static function yesNo($value, int $colorCode = self::RED_FOR_YES): string
    {
        if (intval($value) === 0) {
            $text = __('No', 'acychecker');
            $color = $colorCode === self::RED_FOR_YES ? 'green' : 'red';
        } else {
            $text = __('Yes', 'acychecker');
            $color = $colorCode === self::RED_FOR_YES ? 'red' : 'green';
        }

        if ($colorCode === self::NO_COLOR) {
            return $text;
        }

        return '<span style="color: '.$color.'">'.$text.'</span>';
    }
}
