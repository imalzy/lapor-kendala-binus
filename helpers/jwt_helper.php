<?php
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function generate_jwt($payload, $secret) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $base64UrlHeader = base64url_encode($header);
    $base64UrlPayload = base64url_encode(json_encode($payload));
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = base64url_encode($signature);
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function validate_jwt($jwt, $secret) {
    $tokenParts = explode('.', $jwt);
    if (count($tokenParts) !== 3) return false;
    
    $header = $tokenParts[0];
    $payload = $tokenParts[1];
    $signatureProvided = $tokenParts[2];
    
    $signatureToCheck = hash_hmac('sha256', $header . "." . $payload, $secret, true);
    $base64UrlSignatureToCheck = base64url_encode($signatureToCheck);
    
    if ($base64UrlSignatureToCheck === $signatureProvided) {
        $payloadObj = json_decode(base64url_decode($payload), true);
        if (isset($payloadObj['exp']) && $payloadObj['exp'] < time()) {
            return false; // Token Expired
        }
        return $payloadObj;
    }
    return false;
}
?>