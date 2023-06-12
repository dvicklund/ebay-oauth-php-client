<?php
namespace EbayOauthToken;

require_once 'vendor/autoload.php'; // Assuming you have installed the necessary packages using Composer
require_once __DIR__.'/../src/EbayOauthToken.php';

use PHPUnit\Framework\TestCase;
use EbayOauthToken\EbayOauthToken;

// The mocked file_get_contents function for use when making HTTP requests - if just reading `test.json`,
// allow it to read as normal.
function file_get_contents($filename,
        $use_include_path = false,
        $context = null,
        $offset = 0,
        $length = null): string|false 
{
    if (str_contains($filename, 'test.json')) {
        return \file_get_contents($filename, $use_include_path, $context, $offset, $length);
    } else if (str_contains($filename, 'badpath.json')) {
        return false;
    } else return json_encode([
        'access_token' => 'QWESJAHS12323OP'
    ]);
}

class EbayAuthTokenTest extends TestCase
{
    public function testWithoutOptions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This method accepts an object with filepath or with client id and client secret');
        new EbayOauthToken();
    }

    public function testInputParamsWithoutFilePath()
    {
        $ebayOauthToken = new EbayOauthToken([
            'clientId' => 'PROD1234ABCD',
            'clientSecret' => 'PRODSSSXXXZZZZ',
            'devid' => 'SANDBOXDEVID'
        ]);

        $expectedCredentials = [
            'PRODUCTION' => [
                'clientId' => 'PROD1234ABCD',
                'clientSecret' => 'PRODSSSXXXZZZZ',
                'devid' => 'SANDBOXDEVID',
                'env' => 'PRODUCTION',
                'baseUrl' => 'api.ebay.com'
            ]
        ];

        $this->assertSame($expectedCredentials, $ebayOauthToken->getCredentials());
    }

    public function testGetApplicationTokenMethodUsingFilePath()
    {
        $ebayOauthToken = new EbayOauthToken([
            'filePath' => __DIR__ . '/test.json'
        ]);

        $mockResponse = [
            'access_token' => 'QWESJAHS12323OP'
        ];

        // Note that, since we overrode `file_get_contents` for this namespace, the following function call
        // (getApplicationToken) should not actually touch the eBay API, but will instead return the json-ified
        // version of identical data to $mockResponse
        $appToken = $ebayOauthToken->getApplicationToken('PRODUCTION', 'https://api.ebay.com/oauth/api_scope');

        $this->assertSame($mockResponse, json_decode($appToken, true));
        $this->assertSame('client_credentials', $ebayOauthToken->getGrantType());
    }

    public function testGenerateUserAuthorizationUrlWithIncorrectFilePath()
    {
        $ebayOauthToken = new EbayOauthToken([
            'filePath' => __DIR__ . '/test.json'
        ]);

        $scope = 'https://api.ebay.com/oauth/api_scope';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error while reading credentials');

        $ebayOauthToken->generateUserAuthorizationUrl('PRODUCTION11', $scope);
    }

    public function testGenerateUserAuthorizationUrlWithoutOptions()
    {
        $ebayOauthToken = new EbayOauthToken([
            'filePath' => __DIR__ . '/test.json'
        ]);

        $scope = 'https://api.ebay.com/oauth/api_scope';

        $expectedUrl = 'https://auth.ebay.com/oauth2/authorize?client_id=PROD1234ABCD&redirect_uri=PRODREDIRECT&response_type=code&scope=https://api.ebay.com/oauth/api_scope';

        $this->assertSame($expectedUrl, $ebayOauthToken->generateUserAuthorizationUrl('PRODUCTION', $scope));
    }

    public function testGenerateUserAuthorizationUrlWithIncorrectOptions()
    {
        $ebayOauthToken = new EbayOauthToken([
            'filePath' => __DIR__ . '/test.json'
        ]);

        $scope = 'https://api.ebay.com/oauth/api_scope';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can\'t read optional values');
        $ebayOauthToken->generateUserAuthorizationUrl('PRODUCTION', $scope, 'options');
    }

    public function testGenerateUserAuthorizationUrlWithOptions()
    {
        $ebayOauthToken = new EbayOauthToken([
            'filePath' => __DIR__ . '/test.json'
        ]);

        $scope = 'https://api.ebay.com/oauth/api_scope';
        $options = ['prompt' => 'login', 'state' => 'state'];

        $expectedUrl = 'https://auth.ebay.com/oauth2/authorize?client_id=PROD1234ABCD&redirect_uri=PRODREDIRECT&response_type=code&scope=https://api.ebay.com/oauth/api_scope&prompt=login&state=state';

        $this->assertSame($expectedUrl, $ebayOauthToken->generateUserAuthorizationUrl('PRODUCTION', $scope, $options));
    }

    public function testGenerateUserAuthorizationUrlWithSandboxEnv()
    {
        $ebayOauthToken = new EbayOauthToken([
            'filePath' => __DIR__ . '/test.json'
        ]);

        $scope = 'https://api.ebay.com/oauth/api_scope';

        $expectedUrl = 'https://auth.sandbox.ebay.com/oauth2/authorize?client_id=SAND1234ABCD&redirect_uri=SANDBOXREDIRECT&response_type=code&scope=https://api.ebay.com/oauth/api_scope';

        $this->assertSame($expectedUrl, $ebayOauthToken->generateUserAuthorizationUrl('SANDBOX', $scope));
    }

    public function testExchangeCodeForAccessTokenWithoutCode()
    {
        $ebayOauthToken = new EbayOauthToken([
            'filePath' => __DIR__ . '/test.json'
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Authorization code is required');
        $ebayOauthToken->exchangeCodeForAccessToken('PRODUCTION', null);
    }

    public function testExchangeCodeForAccessTokenWithoutEnvironment()
    {
        $ebayOauthToken = new EbayOauthToken([
            'filePath' => __DIR__ . '/test.json'
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please specify environment - PRODUCTION|SANDBOX');
        $ebayOauthToken->exchangeCodeForAccessToken(null, '12345ABC');
    }

    public function testValidateParamsWithoutScopesShouldFail() 
    {
        $ebayOauthToken = new EbayOauthToken(['filePath' => __DIR__.'/test.json']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Scopes is required');
        $ebayOauthToken->getAccessToken('SANDBOX', 'XXYYZZ1234', null);
    }

    public function testValidateParamsWithoutCredentialsShouldFail() 
    {
        $ebayOauthToken = new EbayOauthToken(['filePath' => __DIR__.'/test.json']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Credentials configured incorrectly');

        $ebayOauthToken->exchangeCodeForAccessToken("NONEXISTENT", "XXYYZZ1234");
    }

    public function testReadJSONFileWithInvalidPathShouldFail()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Error attempting to read config data from file path:");

        $ebayOauthToken = new EbayOauthToken(['filePath' => "badpath.json"]);
    }

    public function testExchangeCodeForAccessToken() 
    {
        $ebayOauthToken = new EbayOauthToken(['filePath' => __DIR__.'/test.json']);

        $authCode = $ebayOauthToken->getApplicationToken('PRODUCTION');

        $res = $ebayOauthToken->exchangeCodeForAccessToken('PRODUCTION', $authCode);

        $this->assertEquals('QWESJAHS12323OP', json_decode($res)->access_token);
    }

    public function testGetAccessToken() 
    {
        $ebayOauthToken = new EbayOauthToken(['filePath' => __DIR__.'/test.json']);

        $res = $ebayOauthToken->getAccessToken('PRODUCTION', 'XXYYZZ1234');

        $this->assertEquals('QWESJAHS12323OP', json_decode($res)->access_token);
    }

    public function testGetAccessTokenWithoutRefreshTokenShouldFail()
    {
        $ebayOauthToken = new EbayOauthToken(['filePath' => __DIR__.'/test.json']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Refresh token is required, to generate refresh token use exchangeCodeForAccessToken method");

        $ebayOauthToken->getAccessToken('PRODUCTION', null);
    }

    public function testSetRefreshToken()
    {
        $ebayOauthToken = new EbayOauthToken(['filePath' => __DIR__.'/test.json']);

        $ebayOauthToken->setRefreshToken('XXYYZZ1234');

        $this->assertEquals('XXYYZZ1234', $ebayOauthToken->getRefreshToken());
    }
}
?>