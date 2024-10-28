<?php

namespace AcyCheckerCmsServices;


class Message
{
    public static function enqueueMessage($message, $type = 'success')
    {
        $type = str_replace(['notice', 'message'], ['info', 'success'], $type);
        $message = is_array($message) ? implode('<br/>', $message) : $message;

        $handledTypes = ['info', 'warning', 'error', 'success'];

        if (in_array($type, $handledTypes)) {
            Miscellaneous::session();
            if (empty($_SESSION['acycmessage'.$type]) || !in_array($message, $_SESSION['acycmessage'.$type])) {
                $_SESSION['acycmessage'.$type][] = $message;
            }
        }

        return true;
    }

    public static function displayMessages()
    {
        $types = ['success', 'info', 'warning', 'error'];
        Miscellaneous::session();
        foreach ($types as $id => $type) {
            if (empty($_SESSION['acycmessage'.$type])) continue;

            Message::display($_SESSION['acycmessage'.$type], $type);
            unset($_SESSION['acycmessage'.$type]);
        }
    }

    public static function display($messages, $type = 'success', $close = true)
    {
        if (empty($messages)) return;
        if (!is_array($messages)) $messages = [$messages];

        foreach ($messages as $id => $message) {
            echo '<div class="acyc__message grid-x acyc__message__'.$type.'">';

            if (is_array($message)) $message = implode('</div><div>', $message);

            echo '<div class="cell auto"><div>'.$message.'</div></div>';

            if ($close) {
                echo '<i data-id="'.Security::escape($id).'" class="cell shrink acyc__message__close acycicon-cancel"></i>';
            }
            echo '</div>';
        }
    }
}
