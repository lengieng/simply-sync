<?php

namespace Lengieng\SimplySync;

class SalesforceCRM
{
    /**
     * Authorization URL
     * @var string
     */
    private $authURL = 'https://login.salesforce.com/services/oauth2/authorize';

    /**
     * Access Token URL
     * @var string
     */
    private $accessTokenURL = 'https://login.salesforce.com/services/oauth2/token';

    /**
     * Endpoint URL
     * @var string
     */
    private $endpointURL = '/services/data/v36.0';

    /**
     * OAuth Access Token
     * @var string
     */
    private $accessToken;

    /**
     * Instance URL
     * @var string
     */
    private $instanceURL;

    /**
     * Client ID
     * @var string
     */
    private $clientId;

    /**
     * Client Secret
     * @var string
     */
    private $clientSecret;

    /**
     * Redirect URI
     * @var string
     */
    private $redirectURI;

    /**
     * Code received from Salesforce after user authorizes our application.
     * @var string
     */
    private $code;

    public function __construct()
    {
        $arg = func_get_args();
        $num = func_num_args();
        if (method_exists($this, $func = '__construct'.$num)) {
            call_user_func_array(array($this, $func), $arg);
        }
    }

    /**
     * Constructor with two parameters.
     * This constructor should be used when directing user to
     *  authorization URL.
     *
     * @param string @clientId      Client ID
     * @param string @redirectURI   Registered redirect URI
     */
    public function __construct2($clientId, $redirectURI)
    {
        $this->clientId = $clientId;
        $this->redirectURI = $redirectURI;
    }

    /**
     * Constructor with three parameters.
     * This constructor must be used when attempting to get
     *  OAuth Access Token.
     *
     * @param string @clientId      Client ID
     * @param string @clientSecret  Client secret
     * @param string @redirectURI   Registered redirect URI
     */
    public function __construct3($clientId, $clientSecret, $redirectURI)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectURI = $redirectURI;
    }

    /**
     * Get authorization URL.
     *
     * @return string   Authorization URL
     */
    public function getAuthURL()
    {
        return $this->authURL;
    }

    /**
     * Set authorization URL.
     *
     * @param string @authURL   Authorization URL string
     *
     * @return void
     */
    public function setAuthURL($authURL)
    {
        $this->authURL = $authURL;
    }

    /**
     * Get instance URL.
     *
     * @return string   Instance URL
     */
    public function getInstanceURL()
    {
        return $this->instanceURL;
    }

    /**
     * Set instance URL.
     *
     * @param string @instanceURL   Instance URL string
     *
     * @return void
     */
    public function setInstanceURL($instanceURL)
    {
        $this->instanceURL = $instanceURL;
    }

    /**
     * Get OAuth Access Token URL.
     *
     * @return string   OAuth Access Token URL
     */
    public function getAccessTokenURL()
    {
        return $this->accessTokenURL;
    }

    /**
     * Set OAuth Access Token URL.
     *
     * @param string @accessTokenURL    Access Token URL string
     *
     * @return void
     */
    public function setAccessTokenURL($accessTokenURL)
    {
        $this->accessTokenURL = $accessTokenURL;
    }

    /**
     * Get endpoint URL.
     *
     * @return string   Endpoint URL
     */
    public function getEndpointURL()
    {
        return $this->endpointURL;
    }

    /**
     * Set endpoint URL.
     *
     * @param string @endpointURL   Endpoint URL string
     *
     * @return void
     */
    public function setEndpointURL($endpointURL)
    {
        $this->endpointURL = $endpointURL;
    }

    /**
     * Get access token.
     *
     * @return string   Access token
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set OAuth access token.
     *
     * @param string @accessToken   Access token
     *
     * @return void
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Get client ID.
     *
     * @return string   Client ID
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set client ID.
     *
     * @param string $clientId  Client Id
     *
     * @return void
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Get client secret.
     *
     * @return string   Client secret
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Set client secret.
     *
     * @param string $clientSecret  Client secret
     *
     * @return void
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * Get redirect URI.
     *
     * @return string   Redirect URI
     */
    public function getRedirectURI()
    {
        return $this->redirectURI;
    }

    /**
     * Set redirect URI.
     *
     * @param string @redirectURI   Redirect URI
     *
     * @return void
     */
    public function setRedirectURI($redirectURI)
    {
        $this->redirectURI = $redirectURI;
    }

    /**
     * Get code.
     *
     * @return string   Code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code.
     *
     * @param string @code  Code
     *
     * @return void
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Perform http 'GET' or 'POST' request.
     *
     * @param string   $url     Request URL
     * @param string   $method  'GET' or 'POST'
     * @param string[] $header  HTTP header
     * @param string[] $params  The key/value pairs of the request parameters
     *
     * @return object Decoded JSON object
     */
    public function request($url, $method = 'GET', $header = array(), $params = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        if (is_array($header) && count($header) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        if ($method === 'POST') {
            if (is_array($params) && count($params)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }
        }

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception(__FUNCTION__ . ': Curl error. ' . $error);
        }

        curl_close($ch);

        return json_decode($response);
    }

    /**
     * Perform endpoint request.
     *
     * @param string   $endpoint    Endpoint URL
     * @param string[] $params      The key/value pairs of the request
     *  parameters.
     *
     * @return object Decoded JSON object
     */
    public function get($endpoint, $params)
    {
        $url = $this->instanceURL . '/' . ltrim($this->endpointURL, '/');
        $url .= '/' . ltrim($endpoint, '/') . '?' . http_build_query($params);

        // Using refresh access token
        $header = array("Authorization: OAuth $this->accessToken");

        return $this->request($url, 'GET', $header, null);
    }

    /**
     * Generate authorization URL.
     * Authorization URL is used to present the user with a login screen and
     *  a confirmation screen to grant app access to user's data.
     *
     * URL Format: https://login.salesforce.com/services/oauth2/authorize?
     *  response_type=code&client_id={CLIENT-ID}&redirect_uri={REDIRECT-URI};
     *
     * @return string   Authorization URL
     */
    public function generateOAuthURL()
    {
        $url = $this->authURL . '?response_type=code&client_id=';
        $url .= $this->clientId . '&redirect_uri=';
        $url .= urlencode($this->redirectURI);

        return $url;
    }

    /**
     * Get OAuth access token from Salesforce.
     * OAuth access token is used to perform request to endpoint.
     *
     * @param string $code  Code received from Salesforce after authorization
     *
     * @return string   OAuth Token (Access Token)
     */
    public function getOAuthToken($code)
    {
        $params = array(
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectURI
        );

        $data = $this->request($this->accessTokenURL, 'POST', null, $params);
        if (property_exists($data, 'access_token')) {
            $this->setAccessToken($data->access_token);
            if (property_exists($data, 'instance_url')) {
                $this->setInstanceURL($data->instance_url);
            } else {
                $errorMsg = __FUNCTION__ . ': Failed to get instance url.';
                if (property_exists($data, 'error_description')) {
                    $errorMsg .= ' ' . $data->error_description;
                }
                throw new \Exception($errorMsg);
            }
            return $data->access_token;
        } else {
            $errorMsg = __FUNCTION__ . ': Failed to get access token.';
            if (property_exists($data, 'error_description')) {
                $errorMsg .= ' ' . $data->error_description;
            }
            throw new \Exception($errorMsg);
        }
    }

    /**
     * Retrieve all contacts
     *
     * @param string[] $fields  Contact fields to be queried
     *
     * @return object[] All contact records
     */
    public function getContacts($fields)
    {
        if (is_array($fields) && count($fields) > 0) {
            $query = 'SELECT ' . implode(",", $fields) . ' from Contact';
            $params = array("q" => $query);
            $endpoint = "/query";
            $data = $this->get($endpoint, $params);
            if (property_exists($data, 'records')) {
                return $data->records;
            }

            throw new \Exception(__FUNCTION__ . ": Records not found.");
        }

        throw new \Exception(__FUNCTION__ . ": invalid field name. " .
                             "Fields must be an array.");
    }
}
