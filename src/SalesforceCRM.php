<?php

namespace Lengieng\SimplySync;

require_once realpath(__DIR__ . '/IRestfulConnection.php');

class SalesforceCRM implements IRestfulConnection
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
     * Salesforce login username
     * @var string
     */
    private $username;

    /**
     * Salesforce login password
     * @var string
     */
    private $password;

    /**
     * Security Token: used in combination with password.
     * @var string
     */
    private $securityToken;

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

    /**
     * Secure
     * @var boolean
     */
    private $secure;

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
     * @param string $clientId      Client ID
     * @param string $redirectURI   Registered redirect URI
     */
    public function __construct2($clientId, $redirectURI)
    {
        $this->clientId = $clientId;
        unset($this->clientSecret);
        $this->redirectURI = $redirectURI;
        $this->secure = true;
    }

    /**
     * Constructor with three parameters.
     * This constructor must be used when attempting to get
     *  OAuth Access Token.
     *
     * @param string $clientId      Client ID
     * @param string $clientSecret  Client secret
     * @param string $redirectURI   Registered redirect URI
     */
    public function __construct3($clientId, $clientSecret, $redirectURI)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectURI = $redirectURI;
        $this->secure = true;
    }

    /**
     * Constructor with five parameters.
     *  Username-Password OAuth Authentication Flow
     *
     * @param string    $clientId       Client Id
     * @param string    $clientSecret   Client secret
     * @param string    $username       Login username
     * @param string    $password       Login password
     * @param string    $securityToken  Security token
     */
    public function __construct5($clientId, $clientSecret, $username, $password, $securityToken)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->username = $username;
        $this->password = $password;
        $this->securityToken = $securityToken;
        unset($this->redirectURI);
        $this->secure = true;
    }

    /**
     * Get authorization URL.
     *
     * @return string   Authorization URL.
     */
    public function getAuthURL()
    {
        return $this->authURL;
    }

    /**
     * Set authorization URL.
     *
     * @param string $authURL   Authorization URL string.
     *
     * @return void.
     */
    public function setAuthURL($authURL)
    {
        $this->authURL = $authURL;
    }

    /**
     * Get instance URL.
     *
     * @return string   Instance URL.
     */
    public function getInstanceURL()
    {
        return $this->instanceURL;
    }

    /**
     * Set instance URL.
     *
     * @param string $instanceURL   Instance URL string.
     *
     * @return void.
     */
    public function setInstanceURL($instanceURL)
    {
        $this->instanceURL = $instanceURL;
    }

    /**
     * Get OAuth Access Token URL.
     *
     * @return string   OAuth Access Token URL.
     */
    public function getAccessTokenURL()
    {
        return $this->accessTokenURL;
    }

    /**
     * Set OAuth Access Token URL.
     *
     * @param string $accessTokenURL    Access Token URL string.
     *
     * @return void.
     */
    public function setAccessTokenURL($accessTokenURL)
    {
        $this->accessTokenURL = $accessTokenURL;
    }

    /**
     * Get endpoint URL.
     *
     * @return string   Endpoint URL.
     */
    public function getEndpointURL()
    {
        return $this->endpointURL;
    }

    /**
     * Set endpoint URL.
     *
     * @param string $endpointURL   Endpoint URL string.
     *
     * @return void.
     */
    public function setEndpointURL($endpointURL)
    {
        $this->endpointURL = $endpointURL;
    }

    /**
     * Get access token.
     *
     * @return string   Access token.
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set OAuth access token.
     *
     * @param string $accessToken   Access token.
     *
     * @return void.
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Get client ID.
     *
     * @return string   Client ID.
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set client ID.
     *
     * @param string $clientId  Client Id.
     *
     * @return void.
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Get client secret.
     *
     * @return string   Client secret.
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Set client secret.
     *
     * @param string $clientSecret  Client secret.
     *
     * @return void.
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * Get redirect URI.
     *
     * @return string   Redirect URI.
     */
    public function getRedirectURI()
    {
        return $this->redirectURI;
    }

    /**
     * Set redirect URI.
     *
     * @param string $redirectURI   Redirect URI.
     *
     * @return void.
     */
    public function setRedirectURI($redirectURI)
    {
        $this->redirectURI = $redirectURI;
    }

    /**
     * Get code.
     *
     * @return string   Code.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code.
     *
     * @param string $code  Code.
     *
     * @return void.
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get login username.
     *
     * @return string   Username.
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username.
     *
     * @param string $username  Login username.
     *
     * @return void.
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get login password.
     *
     * @return string   Password.
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password  Login password.
     *
     * @return void.
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get security token.
     *
     * @return string   Security token.
     */
    public function getSecurityToken()
    {
        return $this->securityToken;
    }

    /**
     * Set security token.
     *
     * @param string $securityToken Security token.
     *
     * @return void.
     */
    public function setSecurityToken($securityToken)
    {
        $this->securityToken = $securityToken;
    }

    /**
     * Set secure connection.
     *
     * @return void.
     */
    public function setSecureConnection($secure)
    {
        $this->secure = $secure;
    }

    /**
     * Check if secure connection is required.
     *
     * @return boolean  true if secure is set.
     */
    public function isSecureConnection()
    {
        return $this->secure;
    }

    /**
     * Perform http 'GET' or 'POST' request.
     *
     * @param string   $url     Request URL.
     * @param string   $method  'GET' or 'POST'.
     * @param string[] $headers HTTP header.
     * @param string[] $params  The key/value pairs of the request parameters.
     *
     * @return object Decoded JSON object.
     */
    public function request($url, $method = 'GET', $headers = array(), $params = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if ($this->isSecureConnection()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        if (count($headers) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        switch ($method) {
            case 'GET':
                if (count($params) > 0) {
                    $url .= '?' . http_build_query($params);
                }
                break;
            case 'POST':
                if (count($params) > 0) {
                    curl_setopt($ch, CURLOPT_POST, true);
                    if (in_array("Content-Type: application/json", $headers)) {
                        // This is a POST request other than requesting for
                        // oAuth Access Token. Therefore, it is a json object.
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                    } else {
                        // This is a POST request for oAuth Access Token.
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                break;
        }
        curl_setopt($ch, CURLOPT_URL, $url);

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
     * Perform endpoint get request.
     *
     * @param string   $endpoint    Endpoint URL.
     * @param string[] $params      The key/value pairs of the request
     *  parameters.
     *
     * @return object Decoded JSON object.
     */
    public function get($endpoint, $params)
    {
        $url = $this->instanceURL . '/' . ltrim($this->endpointURL, '/');
        $url .= '/' . ltrim($endpoint, '/');

        // Using refresh access token
        $header = array("Authorization: OAuth {$this->getAccessToken()}");

        return $this->request($url, 'GET', $header, $params);
    }

    /**
     * Perform endpoint post request.
     *
     * @param string   $endpoint    Endpoint URL.
     * @param string[] $params      The key/value pairs of the request
     *  parameters.
     *
     * @return object Decoded JSON object.
     */
    public function post($endpoint, $params)
    {
        $url = $this->instanceURL . '/' . ltrim($this->endpointURL, '/');
        $url .= '/' . ltrim($endpoint, '/');

        // Using refresh access token
        $header = array(
            "Authorization: OAuth {$this->getAccessToken()}",
            "Content-Type: application/json",
        );

        return $this->request($url, 'POST', $header, $params);
    }

    /**
     * Generate authorization URL.
     * Authorization URL is used to present the user with a login screen and
     *  a confirmation screen to grant app access to user's data.
     *
     * URL Format: https://login.salesforce.com/services/oauth2/authorize?
     *  response_type=code&client_id={CLIENT-ID}&redirect_uri={REDIRECT-URI};
     *
     * @return string   Authorization URL.
     */
    public function generateOAuthURL()
    {
        $url = $this->authURL . '?response_type=code&client_id=';
        $url .= $this->clientId . '&redirect_uri=';
        $url .= urlencode($this->redirectURI);

        return $url;
    }

    /**
     * Get OAuth access token from Salesforce using authorization code.
     * OAuth access token is used to perform request to endpoint.
     *
     * @param string $code  Code received from Salesforce after authorization.
     *
     * @return string   OAuth Token (Access Token)
     */
    public function getOAuthTokenByAuthCode($code)
    {
        $params = array(
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectURI
        );

        $data = $this->request($this->accessTokenURL, 'POST', array(), $params);
        if (property_exists($data, 'access_token')) {
            $this->setAccessToken($data->access_token);
            if (property_exists($data, 'instance_url')) {
                $this->setInstanceURL($data->instance_url);
            } else {
                $errorMsg = __FUNCTION__ . ': failed to get instance url.';
                if (property_exists($data, 'error_description')) {
                    $errorMsg .= " {$data->error_description}";
                }
                throw new \Exception($errorMsg);
            }
            return $data->access_token;
        } else {
            $errorMsg = __FUNCTION__ . ': failed to get access token.';
            if (property_exists($data, 'error_description')) {
                $errorMsg .= " {$data->error_description}";
            }
            throw new \Exception($errorMsg);
        }
    }

    /**
     * Get OAuth access token from Salesforce using password.
     * OAuth access token is used to perform request to endpoint.
     *
     * @return string   OAuth Token (Access Token)
     */
    public function getOAuthTokenByPassword()
    {
        $params = array(
            'grant_type' => 'password',
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'username' => $this->getUsername(),
            'password' => "{$this->getPassword()}{$this->getSecurityToken()}",
        );

        $data = $this->request($this->accessTokenURL, 'POST', array(), $params);
        if (property_exists($data, 'access_token')) {
            $this->setAccessToken($data->access_token);
            if (property_exists($data, 'instance_url')) {
                $this->setInstanceURL($data->instance_url);
            } else {
                $errorMsg = __FUNCTION__ . ': failed to get instance url.';
                if (property_exists($data, 'error_description')) {
                    $errorMsg .= " {$data->error_description}";
                }
                throw new \Exception($errorMsg);
            }
            return $data->access_token;
        } else {
            $errorMsg = __FUNCTION__ . ': failed to get access token.';
            if (property_exists($data, 'error_description')) {
                $errorMsg .= " {$data->error_description}";
            }
            throw new \Exception($errorMsg);
        }
    }

    /**
     * Retrieve all contacts.
     *
     * @param string[] $fields  Contact fields to be queried.
     *
     * @return object   JSON decoded object representing all the contacts.
     */
    public function getContacts($fields = array())
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
        } else {
            // If fields is not specified, assume the following.
            $query = 'SELECT Name,MailingAddress,Phone,MobilePhone,Fax,Email from Contact';
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

    /**
     * Add a contact.
     *
     * @param string[] $fields  Contact fields to be added.
     *
     * @return mixed    Contact id if contact is successfully added;
     *  Otherwise, false.
     */
    public function addContact($fields)
    {
        if (is_array($fields) && count($fields) > 1) {
            $response = $this->post('/sobjects/Contact', $fields);
            if (property_exists($response, "success") && $response->success === true) {
                return $response->id;
            } else {
                return false;
            }
        }

        throw new \Exception(__FUNCTION__ . ": invalid fields. " .
                            "Fields must be an array.");
    }
}
