<?php
function response_api($code, $status, $message, $data = null)
{
    http_response_code($code);
    $response = [
        "status" => $status,
        "message" => $message
    ];

    if ($data != null) {
        $response["data"] = $data;
    }

    echo json_encode($response);
    exit();
}
