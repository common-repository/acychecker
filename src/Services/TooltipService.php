<?php


namespace AcyChecker\Services;


use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;

class TooltipService
{
    public static function tooltip(string $hoveredText, string $textShownInTooltip, string $classContainer = '', string $titleShownInTooltip = '', string $link = '', string $classText = '')
    {
        if (!empty($link)) {
            $hoveredText = '<a href="'.$link.'" title="'.Security::escape($titleShownInTooltip).'" target="_blank">'.$hoveredText.'</a>';
        }

        if (!empty($titleShownInTooltip)) {
            $titleShownInTooltip = '<span class="acyc__tooltip__title">'.$titleShownInTooltip.'</span>';
        }

        return '<span class="acyc__tooltip '.$classContainer.'"><span class="acyc__tooltip__text '.$classText.'">'.$titleShownInTooltip.$textShownInTooltip.'</span>'.$hoveredText.'</span>';
    }

    public static function info($tooltipText, $class = '', $containerClass = '', $classText = '', $warningInfo = false)
    {
        $classWarning = $warningInfo ? 'acyc__tooltip__info__warning' : '';

        return self::tooltip(
            '<span class="acyc__tooltip__info__container '.$class.'"><i class="acyc__tooltip__info__icon acycicon-question-circle-o '.$classWarning.'"></i></span>',
            $tooltipText,
            'acyc__tooltip__info '.$containerClass,
            '',
            '',
            $classText
        );
    }
}
