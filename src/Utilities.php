<?php namespace dvicklund;

include_once('Constants.php');

class Utilities {
    /**
     * Reads a json file at the input `$path` into an associative array and returns it
     * 
     * @param string $path
     * 
     * @return Array
     */
    public static function readJSONFile($path) {
        $JSONString = file_get_contents($path);

        if (!$JSONString) {
            throw new \Exception('Error getting file contents');
        }

        return json_decode($JSONString, true);
    }

    /**
     * Reads the input $opt (options) associative array and massages into usable credentials object,
     * returning the modified array.
     *
     * @param array $opt
     * 
     * @return array
     */
    public static function readOptions($opt) {
        $creds = [];
        if (!array_key_exists('env', $opt)) {
            $opt['env'] = PROD_ENV;
        }
        $opt['baseUrl'] = $opt['env'] == PROD_ENV ? API_BASE_URI : API_SANDBOX_BASE_URI;
        $creds[$opt['env']] = $opt;

        return $creds;
    }

    /**
     * Validate parameters and throw exception if any are invalid
     *
     * @param string|null $env
     * @param string|array|null $scopes
     * @param array $creds
     * 
     * @return void
     */
    public static function validateParams($env, $scopes, $creds) {
        if (!$env) throw new \Exception("Environment is required (PRODUCTION | SANDBOX)");
        if (!$scopes) throw new \Exception("Missing required parameter `scopes`");
        if (!$creds) throw new \Exception("Credentials not processed correctly");
    }
}