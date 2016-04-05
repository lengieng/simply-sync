<?php

namespace Lengieng\SimplySync;

class Instagram
{
    /**
     * Authorization URL
     * @var string
     */
    private $authURL = 'https://api.instagram.com/oauth/authorize';
    
    /**
     * Access Token URL
     * @var string
     */
    private $accessTokenURL = 'https://api.instagram.com/oauth/access_token';
    
    /**
     * Endpoint URL
     * @var string
     */
    private $endpointURL = 'https://api.instagram.com/v1';
    
    /**
     * Scopes property
     * @var string[]
     */
    public $scopes = array('basic', 'public_content', 'follower_list', 'comments', 'relationships', 'likes');
    
    /**
     * OAuth Access Token
     * @var string
     */
    private $accessToken;
    
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
     * Registered Redirect URI
     * @var string
     */
    private $redirectURI;
    
    /**
     * Code received from Instagram after user authorizes our application
     * @var string
     */
    private $code;
    
    /**
     * Enforce Signed Requests (Only use with server-side code)
     * If true, each request to Instagram must include sig parameter.
     * See documentation:
     *     https://www.instagram.com/developer/secure-api-requests/
     * @var boolean
     */
    private $esr = true;
    
    public function __construct()
    {
        $arg = func_get_args();
        $num = func_num_args();
        if (method_exists($this, $func = '__construct'.$num)) {
            call_user_func_array(array($this, $func), $arg);
        }
    }
    
    /**
     * Constructor with one parameter.
     * This constructor should be used only after receiving access token.
     *  Client secret is used to generate sig parameter when requesting to
     *  the endpoint.
     *
     * @param string @clientSecret  Client secret
     */
    public function __construct1($clientSecret)
    {
        $this->clientSecret = $clientSecret;
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
    public function setOAuthURL($authURL)
    {
        $this->authURL = $authURL;
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
     * Check if Enforce Signed Requests is enabled.
     *
     * @return boolean  True if ESR is used.
     */
    public function isESR()
    {
        return $this->esr;
    }
    
    /**
     * Set Enforce Signed Requests.
     *
     * @param boolean $esr  true to enable ESR; Otherwise, false
     *
     * @return void
     */
    public function setESR($esr)
    {
        $this->esr = $esr;
    }
    
    /**
     * Generate signature.
     *
     * @param string   $endpoint   The endpoint URL.
     * @param string[] $params     The key/value pairs of the request
     *  parameters.
     *
     * @return string  Computed signature
     */
    public function generateSig($endpoint, $params)
    {
        $secret = $this->clientSecret;
        $sig = $endpoint;
        ksort($params);
        foreach ($params as $key => $val) {
            $sig .= "|$key=$val";
        }
        return hash_hmac('sha256', $sig, $secret, false);
    }
    
    /**
     * Perform http 'GET' or 'POST' request.
     *
     * @param string   $url     Request URL
     * @param string   $method  'GET' or 'POST'
     * @param string[] $params  The key/value pairs of the request parameters
     *
     * @return object Decoded JSON object
     */
    public function request($url, $method = 'GET', $params = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
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
        $url = $this->endpointURL . '/' . ltrim($endpoint, '/');
        $url .= '?' . http_build_query($params);
        
        if ($this->isESR()) {
            // Generate signature
            $sig = $this->generateSig($endpoint, $params, $this->clientSecret);
            $url .= '&sig=' . $sig;
        }
        
        return $this->request($url, 'GET', null);
    }
    
    /**
     * Generate authorization URL.
     * Authorization URL is used to present the user with a login screen and
     *  a confirmation screen to grant app access to user's data.
     *
     * URL Format: https://api.instagram.com/oauth/authorize/?
     *  client_id=CLIENT-ID&redirect_uri=REDIRECT-URI&response_type=code
     *  &scope=likes+comments
     *
     * @param string[] $scopes  Scopes of access permission
     *
     * @return string   Authorization URL
     */
    public function generateOAuthURL($scopes = array('basic'))
    {
        if (is_array($scopes) && count(array_intersect($scopes, $this->scopes)) == count($scopes)) {
            $url = $this->authURL . '/?client_id=' . $this->clientId;
            $url .= '&redirect_uri=' . urlencode($this->redirectURI);
            $url .= '&response_type=code' . '&scope=' . implode('+', $scopes);
            return $url;
        }
        
        $errorMsg = __FUNCTION__ . ': Failed to generate OAuth URL.';
        $errorMsg .= ' Invalid scope.';
        throw new \Exception($errorMsg);
    }
    
    /**
     * Get OAuth access token from Instagram.
     * OAuth access token is used to perform request to endpoint.
     *
     * @param string $code  Code received from Instagram after authorization
     *
     * @return string   OAuth Token (Access Token)
     */
    public function getOAuthToken($code)
    {
        $params = array(
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectURI,
            'code' => $code,
        );
        
        $data = $this->request($this->accessTokenURL, 'POST', $params);
        if (property_exists($data, 'access_token')) {
            $this->setAccessToken($data->access_token);
            return $data->access_token;
        }
        
        $errorMsg = __FUNCTION__ . ': Failed to get access token.';
        if (property_exists($data, 'error_message')) {
            $errorMsg .= ' ' . $data->error_message;
        }
        throw new \Exception($errorMsg);
    }
    
    /**
     * Get list of users this user follows.
     *  Method      :   GET
     *  Endpoint    :   /users/self/follows
     *  Scope       :   follower_list
     *  Parameters  :   ACCESS_TOKEN
     *
     * @return object[]   Array of users this user follows
     */
    public function getFollowing()
    {
        $endpoint = '/users/self/follows';
        $params = array('access_token' => $this->accessToken);
        
        $response = $this->get($endpoint, $params);
        if ($response->meta->code !== 200) {
            $errorMsg = __FUNCTION__ . ': ' . $response->meta->error_message;
            throw new \Exception($errorMsg);
        }
        
        return $response->data;
    }
    
    /**
     * Get the list of users this user is followed by.
     *  Method      :   GET
     *  Endpoint    :   /users/self/followed-by
     *  Scope       :   follower_list
     *  Parameters  :   ACCESS_TOKEN
     *
     * @return object[]  Array of followers
     */
    public function getFollowers()
    {
        $endpoint = '/users/self/followed-by';
        $params = array('access_token' => $this->accessToken);
        
        $response = $this->get($endpoint, $params);
        if ($response->meta->code !== 200) {
            $errorMsg = __FUNCTION__ . ': ' . $response->meta->error_message;
            throw new \Exception($errorMsg);
        }
        
        return $response->data;
    }
    
    /**
     * Get user information.
     *  Method      :   GET
     *  Endpoint    :   /users/user-id
     *  Scope       :   public_content
     *  Parameters  :   ACCESS_TOKEN
     *
     * @return object   User info
     */
    public function getUserInfo($userId)
    {
        $endpoint = '/users/' . $userId;
        $params = array('access_token' => $this->accessToken);
        
        $response = $this->get($endpoint, $params);
        
        if ($response->meta->code !== 200) {
            $errorMsg = __FUNCTION__ . ': ' . $response->meta->error_message;
            throw new \Exception($errorMsg);
        }
        
        return $response->data;
    }
}
