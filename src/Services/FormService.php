<?php


namespace AcyChecker\Services;


use AcyCheckerCmsServices\Language;
use AcyCheckerCmsServices\Security;

class FormService
{

    public static function select($data, $name, $selected = null, $attribs = null, $optKey = 'value', $optText = 'text', $idtag = false)
    {
        $idtag = str_replace(['[', ']', ' '], '', empty($idtag) ? $name : $idtag);

        $attributes = '';
        if (!empty($attribs)) {
            if (is_array($attribs)) {
                foreach ($attribs as $attribName => $attribValue) {
                    if (is_array($attribValue) || is_object($attribValue)) $attribValue = json_encode($attribValue);
                    $attribName = str_replace([' ', '"', "'"], '_', $attribName);
                    $attributes .= ' '.$attribName.'="'.Security::escape($attribValue).'"';
                }
            } else {
                $attributes = $attribs;
            }
        }

        $dropdown = '<select id="'.Security::escape($idtag).'" name="'.Security::escape($name).'" '.$attributes.'>';

        foreach ($data as $key => $oneOption) {
            $disabled = false;
            if (is_object($oneOption)) {
                $value = $oneOption->$optKey;
                $text = $oneOption->$optText;
                if (isset($oneOption->disable)) {
                    $disabled = $oneOption->disable;
                }
            } else {
                $value = $key;
                $text = $oneOption;
            }

            if (strtolower($value) == '<optgroup>') {
                $dropdown .= '<optgroup label="'.Security::escape($text).'">';
            } elseif (strtolower($value) == '</optgroup>') {
                $dropdown .= '</optgroup>';
            } else {
                $cleanValue = Security::escape($value);
                $cleanText = Security::escape($text);
                $dropdown .= '<option value="'.$cleanValue.'"'.($value == $selected ? ' selected="selected"' : '').($disabled ? ' disabled="disabled"'
                        : '').'>'.$cleanText.'</option>';
            }
        }

        $dropdown .= '</select>';

        return $dropdown;
    }

    /**
     * @param array  $data     Can be in format $data[value] = text or $data[1] = object
     * @param string $name
     * @param array  $selected
     * @param array  $attribs  All attributes to add to the select in this format $data["class"] = "my_class"
     * @param string $optValue Value name identifier to access by $value = object->$optValue
     * @param string $optText  Text name identifier to access by $text = object->$optText
     * @param bool   $translate
     *
     * @return string
     */
    public static function selectMultiple($data, $name, $selected = [], $attribs = [], $optValue = 'value', $optText = 'text')
    {
        if (substr($name, -2) !== '[]') {
            $name .= '[]';
        }

        $attribs['multiple'] = 'multiple';

        $dropdown = '<select name="'.Security::escape($name).'"';
        foreach ($attribs as $attribKey => $attribValue) {
            $dropdown .= ' '.$attribKey.'="'.addslashes($attribValue).'"';
        }
        $dropdown .= '>';

        foreach ($data as $oneDataKey => $oneDataValue) {
            $disabled = '';

            if (is_object($oneDataValue)) {
                $value = $oneDataValue->$optValue;
                $text = $oneDataValue->$optText;

                if (!empty($oneDataValue->disable)) {
                    $disabled = ' disabled="disabled"';
                }
            } else {
                $value = $oneDataKey;
                $text = $oneDataValue;
            }

            if (strtolower($value) == '<optgroup>') {
                $dropdown .= '<optgroup label="'.Security::escape($text).'">';
            } elseif (strtolower($value) == '</optgroup>') {
                $dropdown .= '</optgroup>';
            } else {
                $text = Security::escape($text);
                $dropdown .= '<option value="'.Security::escape($value).'"'.(in_array($value, $selected) ? ' selected="selected"' : '').$disabled.'>'.$text.'</option>';
            }
        }

        $dropdown .= '</select>';

        return $dropdown;
    }

    public static function sortBy($options, $listing, $default = '', $defaultSortOrdering = 'desc')
    {
        $default = empty($default) ? reset($options) : $default;

        $selected = Security::getVar('string', $listing.'_ordering', $default);
        $orderingSortOrder = Security::getVar('string', $listing.'_ordering_sort_order', $defaultSortOrdering);
        $classSortOrder = $orderingSortOrder == 'asc' ? 'acycicon-sort-amount-asc' : 'acycicon-sort-amount-desc';

        $display = '<span class="acyc__color__dark-gray">'.__('Sort by', 'acychecker').'</span>';
        $display .= self::select(
            $options,
            $listing.'_ordering',
            $selected,
            ['class' => 'acyc__select acyc__select__sort'],
            'value',
            'text',
            'acyc__listing__ordering'
        );

        $tooltipText = $orderingSortOrder === 'asc' ? __('Ascending sort (click for descending sort)', 'acychecker') : __('Descending sort (click for ascending sort)', 'acychecker');
        $display .= TooltipService::tooltip('<i class="'.$classSortOrder.'" id="acyc__listing__ordering__sort-order" aria-hidden="true"></i>', $tooltipText);

        $display .= '<input type="hidden" id="acyc__listing__ordering__sort-order--input" name="'.$listing.'_ordering_sort_order" value="'.$orderingSortOrder.'">';

        return $display;
    }

    public static function listingActions($actions, $deleteMessage = '', $ctrl = '')
    {
        $defaultAction = new \stdClass();
        $defaultAction->value = 0;
        $defaultAction->text = __('Choose an action', 'acychecker');
        $defaultAction->disable = true;

        array_unshift($actions, $defaultAction);

        $completeMessage = '<input id="acyc__listing__action__delete-message" value="'.(empty($deleteMessage) ? '' : Security::escape($deleteMessage)).'" type="hidden">';

        $selectAttributes = ['class' => 'acyc__select'];
        if (!empty($ctrl)) {
            $selectAttributes['data-ctrl'] = $ctrl;
            $completeMessage .= ' <input type="hidden" name="return_listing">';
        }

        return '<div class="medium-shrink cell margin-right-1">'.FormService::select(
                $actions,
                '',
                null,
                $selectAttributes,
                'value',
                'text',
                'listing_actions'
            ).$completeMessage.'</div>';
    }
}
