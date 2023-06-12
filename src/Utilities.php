<?php

namespace EbayOauthToken;

function readJSONFile(string $fileName): array
{
    $resolvedPath = file_exists($fileName) ? $fileName : __DIR__ . '/' . $fileName;

    $fileContents = file_get_contents($resolvedPath);
    
    if (!$fileContents) {
        throw new \Exception("Error attempting to read config data from file path: $resolvedPath");
    }

    $configData = json_decode($fileContents, true);

    return $configData;
}

function validateParams(string $environment, array $scopes, array $credentials): void
{
    if (!$environment) {
        throw new \Exception('Please specify environment - PRODUCTION|SANDBOX');
    }
    if (!$scopes) {
        throw new \Exception('Scopes is required');
    }
    if (!$credentials) {
        throw new \Exception('Credentials configured incorrectly');
    }
}

function readOptions(array $options): array
{
    $credentials = [];
    if (!isset($options['env'])) {
        $options['env'] = 'PRODUCTION';
    }
    $options['baseUrl'] = $options['env'] === 'PRODUCTION' ? 'api.ebay.com' : 'api.sandbox.ebay.com';
    $credentials[$options['env']] = $options;
    return $credentials;
}