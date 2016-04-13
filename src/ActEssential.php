<?php

namespace Lengieng\SimplySync;

class ActEssential
{
    /**
     * Endpoint URL
     * @var string
     */
    private $endpointURL = "https://mycloud.act.com/act";

    /**
     * API key
     * @var string
     */
    private $apiKey;

    /**
     * Developer key
     * @var string
     */
    private $devKey;

    /**
     * Secure
     * @var boolean
     */
    private $secure;

    /**
     * Constructor
     *
     * @param string $apiKey    API key
     * @param string $devKey    Developer key
     * @param string $secure    Secure connection if set to true
     *
     * @return void
     */
    public function __construct($apiKey, $devKey, $secure = true)
    {
        $this->apiKey = $apiKey;
        $this->devKey = $devKey;
        $this->secure = $secure;
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
     * @return void
     */
    public function setEndpointURL($endpointURL)
    {
        $this->endpointURL = $endpointURL;
    }

    /**
     * Get API key.
     *
     * @return string   API key
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set API key.
     *
     * @param string $apiKey    API key
     *
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get developer key.
     *
     * @return string   Developer key
     */
    public function getDevKey()
    {
        return $this->devKey;
    }

    /**
     * Set developer key.
     *
     * @param string $devKey    Developer key
     *
     * @return void
     */
    public function setDevKey($devKey)
    {
        $this->devKey = $devKey;
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        // Alternatively, include the following in the headers:
        //  Authorization: Basic base64_encode("{$this->apiKey}:{$this->devKey}")
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->apiKey}:{$this->devKey}");

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
                );
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                curl_setopt($ch, CURLOPT_POST, true);
                if (count($params) > 0) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
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
     * Perform endpoint request.
     *
     * @param string   $endpoint    Endpoint URL.
     * @param string[] $params      The key/value pairs of the request
     *  parameters.
     *
     * @return object   Decoded JSON object.
     */
    public function get($endpoint, $params)
    {
        $url = $this->endpointURL . '/' . ltrim($endpoint, '/');

        return $this->request($url, 'GET', $params);
    }

    /**
     * Get contact by group Id.
     *
     * @param string $groupId   Group Id to which contacts belong.
     *
     * @return object   Decode JSON object representing an array of contacts.
     */
    public function getContactsByGroupId($groupId)
    {
        $query = array(
            "groupid" => $groupId,
        );

        return $this->get('api/contacts', $query);
    }

    /**
     * Retrieve all contacts.
     *
     * @return object   Decoded JSON object representing an array of
     *  all contacts.
     */
    public function getContacts()
    {
        return $this->get('api/contacts', null);
    }

    /**
     * Add a contact.
     *
     * @param string[] $contact The key/value pairs of field containing
     *  information about the contact.
     *
     * @return object   Decoded JSON object representing an array of
     *  all contacts.
     */
    public function addContact($contact = array())
    {
        if (count($contact) > 0) {
            $url = $this->endpointURL . '/api/contacts';
            return $this->request($url, 'POST', $contact);
        }

        throw new \Exception(__FUNCTION__ . ': contact not specified.');
    }
}
