<?php

namespace AcyCheckerCmsServices;


class Content
{
    public static function cmsModal($isIframe, $content, $buttonText, $isButton, $modalTitle = '', $identifier = null, $width = '800', $height = '400')
    {
        // Use the WP's thickbox library
        add_thickbox();

        $class = $isButton ? ' button' : '';

        if ($isIframe) {
            return '<a href="'.$content.'&TB_iframe=true&width='.$width.'&height='.$height.'" class="thickbox'.$class.'">'.$buttonText.'</a>';
        } else {
            if (empty($identifier)) {
                $identifier = 'identifier_'.rand(1000, 9000);
            }

            return '<div id="'.$identifier.'" style="display:none;">'.$content.'</div>
                <a href="#TB_inline?width='.$width.'&height='.$height.'&inlineId='.$identifier.'" class="thickbox'.$class.'">'.$buttonText.'</a>';
        }
    }

    public static function getAlias($name)
    {
        return sanitize_title_with_dashes(remove_accents($name));
    }
}
