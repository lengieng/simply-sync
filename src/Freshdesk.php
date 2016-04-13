<?php

namespace Lengieng\SimplySync;

class Freshdesk
{
    /**
     * Your Freshdesk domain name
     * Format: https://your_domain_name.freshdesk.com
     * @var string
     */
    private $domain;

    /**
     * API key
     * @var string
     */
    private $apiKey;

    /**
     * Login Id
     * @var string
     */
    private $loginId;

    /**
     * Login password
     * @var string
     */
    private $loginPassword;

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
     * This constructor is used when only API key is supplied.
     *
     * @param string @domain    User-specific freshdesk domain name
     * @param string @apiKey    API key
     */
    public function __construct2($domain, $apiKey)
    {
        $this->domain = $domain;
        $this->apiKey = $apiKey;
        $this->secure = true;
        unset($this->loginId);
        unset($this->loginPassword);
    }

    /**
     * Constructor with three parameters.
     * This constructor is used when login Id and password are supplied.
     *
     * @param string @domain        User-specific freshdesk domain name
     * @param string @loginId       Login Id
     * @param string @loginPassword Login password
     */
    public function __construct3($domain, $loginId, $loginPassword)
    {
        $this->domain = $domain;
        $this->loginId = $loginId;
        $this->loginPassword = $loginPassword;
        $this->secure = true;
        unset($this->apiKey);
    }

    /**
     * Determine if API key is set and ready for using.
     *
     * @return boolean  True if API key is ready for using; Otherwise, false.
     */
    public function useApiKey()
    {
        return isset($this->apiKey) && strlen($this->apiKey) > 0 ? true : false;
    }

    /**
     * Get API key.
     *
     * @return string   API key.
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set API key.
     *
     * @param string    API key.
     *
     * @return void.
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get login Id.
     *
     * @return string   Login Id.
     */
    public function getLoginId()
    {
        return $this->loginId;
    }

    /**
     * Set login Id.
     *
     * @param string    Login Id (Email address).
     *
     * @return void.
     */
    public function setLoginId($loginId)
    {
        $this->loginId = $loginId;
    }

    /**
     * Get login password.
     *
     * @return string   Login password.
     */
    public function getLoginPassword()
    {
        return $this->loginPassword;
    }

    /**
     * Set login password.
     *
     * @param string    Login password.
     *
     * @return void.
     */
    public function setLoginPassword($loginPassword)
    {
        $this->loginPassword = $loginPassword;
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
     * Generate resource endpoint URI.
     *
     * @return string   Endpoint URI.
     */
    public function getEndpointURI()
    {
        return $this->domain . '/api/v2';
    }

    /**
     * Decode a chunked API response (responses with Transfer-Encoding: chunked).
     *
     * @param string $string The string to decode.
     *
     * @return string The decoded response.
     */
    public static function decodeChunks($string)
    {
        $chunks = explode("\r\n", trim($string));
        $decoded = '';
        if (count($chunks) > 1) {
            for ($i = 0; $i < count($chunks) - 1; $i += 2) {
                if (hexdec($chunks[$i]) != strlen($chunks[$i + 1])) {
                    return $string;
                } else {
                    $decoded .= $chunks[$i + 1];
                }
            }
            return $decoded;
        } else {
            return $string;
        }
    }

    /**
     * Decode a raw response.
     *
     * @param string $response The raw response content.
     *
     * @return array An array containing the the parsed response, including the
     * status code and message and the response body.
     */
    private static function decodeResponse($response)
    {
        if (strpos($response, "\r\n\r\n") === false) {
            return false;
        }

        list($headers, $contentBody) = explode("\r\n\r\n", $response, 2);
        $headers = explode("\r\n", $headers);
        $statusLine = $headers[0];

        list($protocol, $code, $status) = explode(' ', $statusLine, 3);

        $data = array(
            'protocol' => $protocol,
            'code'     => intval($code),
            'status'   => $status,
            'response' => strlen(trim($contentBody)) > 0 ? json_decode(self::decodeChunks($contentBody), true) : false,
        );

        return $data;
    }

    /**
     * Perform http 'GET' or 'POST' request.
     *
     * @param string $url       Request URL.
     * @param string $method    'GET' or 'POST'.
     * @param array  $params    An array of key/value strings.
     *
     * @return object Decoded JSON object.
     */
    public function request($url, $method = 'GET', $params = array())
    {
        $ch = curl_init();

        if ($this->useApiKey()) {
            // If API key is used, password is not needed.
            // Just set dummy password here.
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->getApiKey()}:X");
        } else {
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->getLoginId()}:{$this->getLoginPassword()}");
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        if ($this->isSecureConnection()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        switch ($method) {
            case 'GET':
                $headers = array(
                    "Connection: close",
                    "Accept: application/json",
                );
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                if (count($params) > 0) {
                    $url .= '?' . http_build_query($params);
                }
                break;
            case 'POST':
                $headers = array(
                    "Connection: close",
                    "Content-Type: application/json",
                );
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                curl_setopt($ch, CURLOPT_POST, true);
                if (count($params) > 0) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                }
                break;
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);
        $data = self::decodeResponse($response);
        if ($data === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception(__FUNCTION__ . ': Curl error.' . $error);
        } elseif ($data['code'] >= 400) {
            curl_close($ch);
            throw new \Exception(__FUNCTION__ . ': ' . $data['status']);
        }

        curl_close($ch);

        return $data['response'];
    }

    /**
     * Perform endpoint get request.
     *
     * @param string   $endpoint    Endpoint URL.
     * @param string[] $params      The key/value pairs of the request
     *  parameters.
     *
     * @return object   Decoded JSON object.
     */
    public function get($endpoint, $params)
    {
        $url = $this->getEndpointURI() . '/' . ltrim($endpoint, '/');

        return $this->request($url, 'GET', $params);
    }

    /**
     * Retrieve all contacts.
     *
     * @return object   Decoded JSON object representing an array of all
     *  contacts.
     */
    public function getContacts()
    {
        return $this->get('contacts', null);
    }
}
