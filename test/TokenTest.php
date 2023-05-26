<?php namespace Tests;

require __DIR__ . "/../src/EbayOauthToken.php";

use PHPUnit\Framework\TestCase;
use dvicklund\EbayOauthToken;

class TokenTest extends TestCase 
{
    // TODO: this should probably actually fail, we want the required params in the array.
    public function testEbayAuthTokenInitializationWithArbitraryParams() {
        $this->assertInstanceOf(EbayOauthToken::class, new EbayOauthToken(["test"]));
    }

    public function testInitializationWithoutOptionsShouldFail() {
        $this->expectException(\ArgumentCountError::class);

        $token = new EbayOauthToken();
    }

    public function testInitializeWithoutFilePath() {
        $ebayOauthToken = new EbayOauthToken([
            'clientId' => 'PROD1234ABCD',
            'clientSecret' => 'PRODSSSXXXZZZZ',
            'devId' => 'SANDBOXDEVID',
        ]);

        $this->assertEquals([
            'PRODUCTION' => [
                'clientId' => 'PROD1234ABCD',
                'clientSecret' => 'PRODSSSXXXZZZZ',
                'devId' => 'SANDBOXDEVID',
                'env' => 'PRODUCTION',
                'baseUrl' => 'api.ebay.com',    
            ],
        ], 
        $ebayOauthToken->getCredentials());
    }

    public function testInitializeWithFilePath() {
        $ebayAuthToken = new EbayOauthToken([
            'filePath' => __DIR__.'/test.json',
        ]);

        $this->assertEquals([
            'PRODUCTION' => [
                'clientId' => 'PROD1234ABCD',
                'clientSecret' => 'PRODSSSXXXZZZZ',
                'devId' => 'SANDBOXDEVID',
                'redirectUri' => 'PRODREDIRECT',
                'baseUrl' => 'api.ebay.com',    
            ],
            'SANDBOX' => [
                "clientId" => "SAND1234ABCD",
                "clientSecret" => "SANDBOXSSSXXXZZZZ",
                "devId" => "SANDBOXDEVID",
                "redirectUri" => "SANDBOXREDIRECT",
                "baseUrl" => "api.sandbox.ebay.com"
            ]
        ],
        $ebayAuthToken->getCredentials());
    }

    public function testGetApplicationTokenUsingFilePath() {
        $ebayAuthToken = new EbayOauthToken(['filePath' => __DIR__.'/test.json']);
        $path = 'identity/v1/oauth2/token';
        $host = 'api.ebay.com';
        
    }
}