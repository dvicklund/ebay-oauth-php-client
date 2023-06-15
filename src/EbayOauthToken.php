<?php

namespace EbayOauthToken;

require_once 'Request.php';
require_once 'Utilities.php';

class EbayOauthToken
{
    private $credentials;
    private $grantType;
    private $scope;
    private $refreshToken;

    /**
     * Create an eBay OAuth instance
     *
     * @param array<mixed> $options - Configuration options object
     * @param string $options->clientId - eBay App ID
     * @param string $options->clientSecret - eBay CertId
     * @param string $options->env - Environment (PRODUCTION | SANDBOX)
     * @param string $options->redirectUri - Redirect url for your eBay app
     * @param array<string> $options->scopes - Array of scopes -- for details: 
     *                      https://developer.ebay.com/api-docs/static/oauth-scopes.html
     * @return EbayOauthToken
     */
    public function __construct($options = null)
    {
        if (!$options) {
            throw new \Exception('This method accepts an object with filepath or with client id and client secret');
        }
        // get user credentials.
        $this->credentials = @$options['filePath'] ? readJSONFile($options['filePath']) : readOptions($options);
        $this->grantType = '';
    }

    /**
     * Generates an application access token for client credentials grant flow
     *
     * @param string $environment - Environment (PRODUCTION | SANDBOX)
     * @param array<string> $scopes - Array of scopes for which to generate the access token
     * @return string|null - Returns application token in JSON string, or null on failure
     */
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

    /**
     * Generates the user consent authorization URL
     *
     * @param string $environment - Environment (PRODUCTION | SANDBOX)
     * @param array<string> $scopes - Array of scopes for which to generate the token
     * @param array<mixed> $options - Optional config values
     * @param string $options->state - Custom state falue
     * @param boolean $options->prompt - Force login flow
     * @return string - User consent URL
     */
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

    /**
     * Exchanges an authorization code for an access token
     *
     * @param string $environment - Environment (PRODUCTION | SANDBOX)
     * @param string $code - Authorization code generated from browser using generateUserAuthorizationUrl()
     * @return string|null - Returns access token in JSON string form on success, or null on failure
     */
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

    /**
     * Uses a refresh token to update a user's access token, updating an expired access token
     *
     * @param string $environment - Environment (PRODUCTION | SANDBOX)
     * @param string $refreshToken - Refresh token
     * @param array<string> $scopes - Array of scopes for which to generate the token
     * @return string|null - Returns new access token in JSON string form on success, or null on failure
     */
    public function getAccessToken($environment, $refreshToken = null, $scopes = ['https://api.ebay.com/oauth/api_scope'])
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

    /**
     * Sets the refresh token value on this instance
     *
     * @param string $refreshToken
     * @return string - Returns the newly set refresh token value
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
        return $this->refreshToken;
    }

    /**
     * Get the current refresh token value of this instance
     *
     * @return string - Returns the current refresh token
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Gets the credentials array for this instance
     *
     * @return array<mixed> - Returns the current credentials array
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Get the grant type for this instance
     *
     * @return string - Returns the current grant type
     */
    public function getGrantType()
    {
        return $this->grantType;
    }
}
