<?php
/**
 * Global Monk namespace.
 */
namespace Monk;

use Requests_Session;
use Requests_Exception_HTTP;

use Monk\Cms\Exception;

/**
 * A PHP client for accessing the MonkCMS API in non-website environments.
 *
 * @author Monk Development, Inc.
 */
class Cms
{
    /**
     * Default config values to fall back to when a value isn't set.
     *
     * @var array
     */
    private static $defaultConfig = array(
        'request'    => null,
        'siteId'     => null,
        'siteSecret' => null,
        'url'        => 'http://api.monkcms.com'
    );

    /**
     * Config values.
     *
     * @var array
     */
    private $config;

    /**
     * Constructor.
     *
     * @param  array $config Config values.
     * @return self
     */
    public function __construct(array $config = array())
    {
        $this->setConfig($config);
    }

    /**
     * Set the default config values to fall back to when a value isn't set.
     *
     * The new values are merged with the old ones, so not all values must be
     * specified.
     *
     * @param  array $defaultConfig
     * @return array New default config values.
     */
    public static function setDefaultConfig(array $defaultConfig)
    {
        self::$defaultConfig = array_merge(self::$defaultConfig, $defaultConfig);

        return self::$defaultConfig;
    }

    /**
     * Set the config values.
     *
     * If a value isn't set, the default config value is used.
     *
     * @param  array $config
     * @return self
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge(self::$defaultConfig, $config);

        return $this;
    }

    /**
     * Get the config values.
     *
     * Includes the default config values for any values that weren't set.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the configured `Requests_Session` instance for making HTTP requests.
     *
     * If one isn't configured, a new one is instantiated.
     *
     * @return \Requests_Session
     */
    private function getRequestsSession()
    {
        if (!$this->config['request']) {
            $this->config['request'] = new Requests_Session();
        }

        return $this->config['request'];
    }

    /**
     * Build query params in the format required by the API.
     *
     * @param  array $queryParams Param name => value associative array.
     * @return array
     */
    private static function buildRequestQueryParams(array $queryParams)
    {
        $query = array();
        $count = 0;

        foreach ($queryParams as $key => $value) {
            $queryParam = "{$key}_:_{$value}";

            if ($key == 'module') {
                $queryParam = $value;
            } elseif ($value === true) {
                $queryParam = $key;
            }

            $query["arg{$count}"] = $queryParam;
            $count++;
        }

        return $query;
    }

    /**
     * Build a request query string with query params.
     *
     * @param  array $queryParams Param name => value associative array.
     * @return string
     */
    private function buildRequestQueryString(array $queryParams)
    {
        $config = $this->getConfig();

        $query = array();

        $query['SITEID'] = $config['siteId'];
        $query['NR'] = count($queryParams);

        $query = array_merge($query, self::buildRequestQueryParams($queryParams));

        return http_build_query($query);
    }

    /**
     * Build a request URL with query params.
     *
     * @param  array $queryParams Param name => value associative array.
     * @return string
     */
    private function buildRequestUrl(array $queryParams)
    {
        $config = $this->getConfig();
        $queryString = $this->buildRequestQueryString($queryParams);

        return "{$config['url']}/Clients/ekkContent.php?{$queryString}";
    }

    /**
     * Build the HTTP basic authentication option for `Requests_Session`.
     *
     * @return array `auth` option.
     */
    private function buildRequestAuth()
    {
        $config = $this->getConfig();

        return array('auth' => array($config['siteId'], $config['siteSecret']));
    }

    /**
     * Make a request to the API.
     *
     * @param  array $queryParams Param name => value associative array.
     * @return array JSON-decoded associative array.
     * @throws Exception If the request fails.
     */
    private function request(array $queryParams)
    {
        $queryParams['json'] = true;

        $request = $this->getRequestsSession();

        $response = $request->get($this->buildRequestUrl($queryParams), array(), $this->buildRequestAuth());

        try {
            $response->throw_for_status();
        } catch (Requests_Exception_HTTP $requestsException) {
            throw new Exception($requestsException->getReason(), $requestsException->getCode());
        }

        $responseBody = substr($response->body, 10);

        return json_decode($responseBody, true);
    }

    /**
     * Build the query params from function arguments.
     *
     * @param  array $args Function arguments to parse.
     * @return array Param name => value associative array.
     */
    private static function buildQueryParamsFromArgs(array $args)
    {
        $queryParams = $args[0];

        if (is_string($queryParams)) {
            $queryParams = explode('/', $queryParams);

            $queryParams = array(
                'module'  => $queryParams[0],
                'display' => $queryParams[1],
                'find'    => isset($queryParams[2]) ? $queryParams[2] : null
            );

            if (isset($args[1])) {
                $queryParams = array_merge($queryParams, $args[1]);
            }
        }

        return array_filter($queryParams);
    }

    /**
     * Request content from the API.
     *
     * Parameters can be in one of two formats:
     *
     *   - A slash-separated string (`module/display[/find]`), followed by an
     *     optional array of additional parameters.
     *   - An array with all of the parameters.
     *
     * @return array JSON-decoded associative array.
     * @throws Exception If the request fails.
     */
    public function get()
    {
        $queryParams = self::buildQueryParamsFromArgs(func_get_args());

        return $this->request($queryParams);
    }
}
