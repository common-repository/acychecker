<?php

namespace AcyChecker\Services;

use AcyCheckerCmsServices\Security;

class ModalService
{
    public static function modal($button, $data, $id = null, $attributesModal = '', $attributesButton = [], $isButton = true, $isLarge = true)
    {
        if (empty($id)) {
            $id = 'acycmodal_'.rand(1000, 9000);
        }

        $buttonParams = '';
        foreach ($attributesButton as $oneAttribute => $oneValue) {
            $buttonParams .= ' '.$oneAttribute.'="'.Security::escape($oneValue).'"';
        }

        $modal = $isButton ? '<button type="button" data-open="'.$id.'" '.$buttonParams.'>'.$button.'</button>' : $button;
        $modal .= '<div class="reveal" '.($isLarge ? 'data-reveal-larger' : '').' id="'.$id.'" '.$attributesModal.' data-reveal>';
        $modal .= $data;
        $modal .= '<button class="close-button" data-close aria-label="Close reveal" type="button">';
        $modal .= '<span aria-hidden="true">&times;</span>';
        $modal .= '</button></div>';

        return $modal;
    }
}
