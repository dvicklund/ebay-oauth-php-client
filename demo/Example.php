<?php

use dvicklund\EbayOauthToken;

// Pass credentials through a config file
$ebayAuthToken = new EbayOauthToken([
    'filepath' => 'demo/ebay-config-sample.json',
]);

// Pass credentials through options array
$ebayAuthToken = new EbayOauthToken([
    'clientId' => '---Client ID---',
    'clientSecret' => '---Client Secret---',
    'redirectUri' => '---Redirect URI Name---',
]);

$clientScope = 'https://api.ebay.com/oauth/api_scope';

$token = $ebayAuthToken->getApplicationToken('PRODUCTION', $clientScope);