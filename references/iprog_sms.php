<?php
// iprog_sms.php

function sendOTP($phone) {
    $apiToken = "31975a73af3c2c43ba0bea92d9c5200fd623f5ea"; 
    $url = "https://sms.iprogtech.com/api/v1/otp/send_otp";

    $payload = json_encode([
        "api_token" => $apiToken,
        "phone_number" => $phone
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function verifyOTP($phone, $otp) {
    $apiToken = "31975a73af3c2c43ba0bea92d9c5200fd623f5ea"; 
    $url = "https://sms.iprogtech.com/api/v1/otp/verify_otp";

    $payload = json_encode([
        "api_token" => $apiToken,
        "phone_number" => $phone,
        "otp" => $otp
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
?>
