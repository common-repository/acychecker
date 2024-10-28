<?php

namespace AcyCheckerCmsServices;


class Ajax
{
    public static function sendAjaxResponse($message = '', $data = [], $success = true)
    {
        $response = [
            'message' => $message,
            'data' => $data,
            'status' => $success ? 'success' : 'error',
        ];

        wp_send_json($response);
    }
}
