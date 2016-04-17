<?php

namespace Lengieng\SimplySync;

require_once 'IRestfulConnection.php';

class TactileCRM implements IRestfulConnection
{
    /**
     * Service URL
     * @var string
     */
    private $serviceURL;

    /**
     * User API Token
     * @var string
     */
    private $token;

    /**
     * Secure
     * @var boolean
     */
    private $secure;

    /**
     * Constructor
     *
     * @param string @serviceURL    Service URL.
     * @param string $token         API token.
     * @param boolean @secure       Secure connection.
     *
     * @return void
     */
    public function __construct($serviceURL, $token, $secure = true)
    {
        $this->serviceURL = $serviceURL;
        $this->token = $token;
        $this->secure = $secure;
    }

    /**
     * Get service URL.
     *
     * @return string   Service URL.
     */
    public function getServiceURL()
    {
        return $this->serviceURL;
    }

    /**
     * Set service URL.
     *
     * @param string $servceURL  Service URL.
     *
     * @return void.
     */
    public function setServiceURL($serviceURL)
    {
        $this->serviceURL = $serviceURL;
    }

    /**
     * Get user API token.
     *
     * @return string   User API token.
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set user API token.
     *
     * @param string $token  User API token.
     *
     * @return void.
     */
    public function setToken($token)
    {
        $this->token = $token;
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
     * @param string[] $header  HTTP header.
     * @param array    $params  An array of key/value strings.
     *
     * @return object Decoded JSON object.
     */
    public function request($url, $method = 'GET', $header = array(), $params = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        if ($this->isSecureConnection()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        if (count($header) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        switch ($method) {
            case 'GET':
                $url .= '?' . http_build_query($params);
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
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
     * @param string    $endpoint    Endpoint URL.
     * @param array     $params      The key/value pairs of the request
     *  parameters.
     *
     * @return object   Decoded JSON object.
     */
    public function get($endpoint, $params)
    {
        $url = $this->serviceURL . '/' . ltrim($endpoint, '/');
        $headers = array(
            "Connection: close",
            "Accept: application/json",
        );

        return $this->request($url, 'GET', $headers, $params);
    }

    /**
     * Perform endpoint post request.
     *
     * @param string    $endpoint    Endpoint URL.
     * @param array     $params      The key/value pairs of the request
     *  parameters.
     *
     * @return object   Decoded JSON object.
     */
    public function post($endpoint, $params)
    {
        $url = $this->serviceURL . '/' . ltrim($endpoint, '/');
        $url .= '?api_token=' . $this->getToken();
        $headers = array(
            "Connection: close",
            "Content-Type: application/json",
        );

        return $this->request($url, 'POST', $headers, $params);
    }

    /**
     * Get people object by page number.
     *
     * @param integer $pageNum  Page number to return.
     * @param integer $limit    Number of results per page to return.
     *
     * @return object   Decoded JSON object representing an array of people per
     *  given page number.
     */
    protected function getContactByPage($pageNum, $limit = 30)
    {
        $query = array(
            'api_token' => $this->getToken(),
            'limit' => $limit,
            'page' => $pageNum,
        );
        return $this->get('/people', $query);
    }

    /**
     * Retrieve all contacts.
     *
     * @return object Decoded JSON object representing an array of all people.
     */
    public function getContacts()
    {
        $data = array();
        $cur_page = 1;

        do {
            $page = $this->getContactByPage($cur_page);
            if ($page->status === "error") {
                throw new \Exception(__FUNCTION__ . ': Failed to get people object.');
            }

            for ($i = 0; $i < count($page->people); $i++) {
                $data[] = $page->people[$i];
            }

            $cur_page = (int)$page->cur_page + 1;
        } while ($page->cur_page < $page->num_pages);

        return $data;
    }

    /**
     * Add a contact.
     *
     * @param array $contact    An array of array of Person object.
     *  Consult API documentation for more detail.
     *
     * @return boolean  true if successful; Otherwise, false.
     */
    public function addContact($contact)
    {
        $response = $this->post('/people/save', $contact);
        if (property_exists($response, "status") && $response->status === "success")
            return true;
        else
            return false;
    }
}
