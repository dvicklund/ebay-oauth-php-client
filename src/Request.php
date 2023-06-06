<?php

namespace EbayOauthToken;

function postRequest($data, $ebayAuthToken)
{
    $encodedStr = base64_encode("{$ebayAuthToken['clientId']}:{$ebayAuthToken['clientSecret']}");
    $auth = "Basic {$encodedStr}";

    $options = [
        'http' => [
            'header' => "Content-Length: " . strlen($data) . "\r\n" .
                        "Content-Type: application/x-www-form-urlencoded\r\n" .
                        "authorization: {$auth}\r\n",
            'method' => 'POST',
            'content' => $data
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents("https://{$ebayAuthToken['baseUrl']}/identity/v1/oauth2/token", false, $context);

    return $response;
}