<?php namespace dvicklund;

// HTTP Header Constants
const HEADER_CONTENT_TYPE = 'application/x-www-form-urlencoded';
const HEADER_PREFIX_BASIC = 'Basic ';
const HEADER_AUTHORIZATION = 'Authorization';

// HTTP Request
const PAYLOAD_VALUE_CLIENT_CREDENTIALS = 'client_credentials';
const PAYLOAD_VALUE_AUTHORIZATION_CODE = 'authorization_code';
const PAYLOAD_REFRESH_TOKEN = 'refresh_token';
const PAYLOAD_STATE = 'state';

// Web End point
const OAUTHENVIRONMENT_WEBENDPOINT_PRODUCTION = 'https://auth.ebay.com/oauth2/authorize';
const OAUTHENVIRONMENT_WEBENDPOINT_SANDBOX = 'https://auth.sandbox.ebay.com/oauth2/authorize';

// API End Point
const OAUTHENVIRONMENT_APIENDPOINT_SANDBOX = 'https://api.sandbox.ebay.com/identity/v1/oauth2/token';
const OAUTHENVIRONMENT_APIENDPOINT_PRODUCTION = 'https://api.ebay.com/identity/v1/oauth2/token';

// Scopes
const CLIENT_CRED_SCOPE = 'https://api.ebay.com/oauth/api_scope';

// Environments
const PROD_ENV = 'PRODUCTION';
const SANDBOX_ENV = 'SANDBOX';

// API Base uris
const API_BASE_URI = 'api.ebay.com';
const API_SANDBOX_BASE_URI = 'api.sandbox.ebay.com';
