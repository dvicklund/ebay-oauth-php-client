<?php namespace dvicklund;

use PHPUnit\Framework\Constraint\ArrayHasKey;
use dvicklund\Utilities as Util;

class EbayOauthToken {
    protected $credentials = null;
    protected $grantType = null;

    public function __construct(array $options) {
        if (!$options) {
            throw new \Exception("Error - options object is required, containing a filepath or client id and client secret");

            // TODO: get credentials from file or input object
        }

        $this->credentials = array_key_exists('filePath', $options) ? Util::readJSONFile($options['filePath']) : Util::readOptions($options);
        $this->grantType = '';
    }

    public function getApplicationToken($env, $scopes = CLIENT_CRED_SCOPE) {
        
    }

    public function getCredentials() {
        return $this->credentials;
    }
}