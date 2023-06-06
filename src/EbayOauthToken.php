<?php

namespace EbayOauthToken;

require_once 'Request.php';
require_once "Utilities.php";

class EbayOauthToken
{
    private $credentials;
    private $grantType;
    private $scope;
    private $refreshToken;

    public function __construct($options = null)
    {
        if (!$options) {
            throw new \Exception('This method accepts an object with filepath or with client id and client secret');
        }
        // get user credentials.
        $this->credentials = @$options['filePath'] ? readJSONFile($options['filePath']) : readOptions($options);
        $this->grantType = '';
    }

    public function getApplicationToken($environment, $scopes = ['https://api.ebay.com/oauth/api_scope'])
    {
        validateParams($environment, $scopes, $this->credentials);

        $this->grantType = 'client_credentials';
        $this->scope = is_array($scopes) ? implode(' ', $scopes) : $scopes;
        
        // url-encoded query string
        $data = http_build_query([
            'grant_type' => $this->grantType,
            'scope' => $this->scope
        ]);
        return postRequest($data, $this->credentials[$environment]);
    }

    public function generateUserAuthorizationUrl($environment, $scopes, $options = null)
    {
        validateParams($environment, $scopes, $this->credentials);

        $credentials = @$this->credentials[$environment];
        if (!$credentials) throw new \Exception('Error while reading credentials');
        if (!$credentials['redirectUri']) throw new \Exception('redirect_uri is required for redirection after sign in \n check here: https://developer.ebay.com/api-docs/static/oauth-redirect-uri.html');
        
        if ($options && !is_array($options)) throw new \Exception('Can\'t read optional values');

        $this->scope = is_array($scopes) ? implode(' ', $scopes) : $scopes;
        
        $prompt = $options['prompt'] ?? '';
        $state = $options['state'] ?? '';
        
        $queryParam = "client_id={$credentials['clientId']}";
        $queryParam .= "&redirect_uri={$credentials['redirectUri']}";
        $queryParam .= "&response_type=code";
        $queryParam .= "&scope={$this->scope}";
        $queryParam .= $prompt ? "&prompt={$prompt}" : '';
        $queryParam .= $state ? "&state={$state}" : '';
        
        $baseUrl = $environment === 'PRODUCTION' ? 'https://auth.ebay.com/oauth2/authorize'
            : 'https://auth.sandbox.ebay.com/oauth2/authorize';
        return $baseUrl . '?' . $queryParam;
    }

    public function exchangeCodeForAccessToken($environment, $code)
    {
        if (!$code) {
            throw new \Exception('Authorization code is required');
        }
        validateParams($environment, true, @$this->credentials[$environment]);
        $credentials = $this->credentials[$environment];
        $data = "code={$code}&grant_type=authorization_code&redirect_uri={$credentials['redirectUri']}";
        return postRequest($data, $credentials);
    }

    public function getAccessToken($environment, $refreshToken, $scopes)
    {
        $token = $refreshToken ?: $this->getRefreshToken();
        validateParams($environment, $scopes, $this->credentials);
        $this->scope = is_array($scopes) ? implode(' ', $scopes) : $scopes;
        if (!$token) {
            throw new \Exception('Refresh token is required, to generate refresh token use exchangeCodeForAccessToken method');
        }
        $data = "refresh_token={$token}&grant_type=refresh_token&scope={$this->scope}";
        return postRequest($data, $this->credentials[$environment]);
    }

    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function getCredentials()
    {
        return $this->credentials;
    }

    public function getGrantType()
    {
        return $this->grantType;
    }
}