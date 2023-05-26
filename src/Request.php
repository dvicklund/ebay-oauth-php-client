<?php namespace dvicklund;

use Constants;

function postRequest($data, $ebayAuthToken) {
    $dataLength = sizeof($data);
    $encodedString = base64_encode($ebayAuthToken->clientId.":".$ebayAuthToken->clientSecret);
    $authString = "Basic $encodedString";
    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Length: $dataLength\r\n".
                        "Content-Type: application/x-www-form-urlencoded\r\n".
                        "authorization: $authString"
        ]
    ];
}
