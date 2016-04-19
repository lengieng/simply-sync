<?php

namespace Lengieng\SimplySync;

require_once 'IRestfulConnection.php';

class Solve360 implements IRestfulConnection
{
    /**
     * Endpoint URL
     * @var string
     */
    public $endpointURL = 'https://secure.solve360.com';

    /**
     * User email
     * @var string
     */
    private $email;

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
     * @param string $email     Email address.
     * @param string $token     API token.
     * @param string $secure    Secure connection if set to true.
     */
    public function __construct($email, $token, $secure = true)
    {
        $this->email = $email;
        $this->token = $token;
        $this->secure = $secure;
    }

    /**
     * Get user email.
     *
     * @return string   User email.
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set user email used for login.
     *
     * @param string $email     User email.
     *
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     * @param string[] $params  The key/value pairs of the request parameters.
     *
     * @return object Decoded JSON object.
     */
    public function request($url, $method = 'GET', $header = array(), $params = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        // Alternatively, include the following in the headers:
        //  Authorization: Basic base64_encode("{$this->getEmail()}:{$this->getToken()}")
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->getEmail()}:{$this->getToken()}");

        if ($this->isSecureConnection()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        if (count($header) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        if ($method === 'POST') {
            if (strlen($params)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
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
     * Perform endpoint get request.
     *
     * @param string    $endpoint    Endpoint URL.
     * @param array     $params      The key/value pairs of the request
     *  parameters.
     *
     * @return object Decoded JSON object.
     */
    public function get($endpoint, $params)
    {
        $url = $this->endpointURL . '/' . ltrim($endpoint, '/');
        // By default, Solve360 returns XML. We want JSON here.
        $header = array("Accept: application/json");

        return $this->request($url, 'GET', $header, $params);
    }

    /**
     * Perform endpoint post request.
     *
     * @param string    $endpoint    Endpoint URL.
     * @param array     $params      The key/value pairs of the request
     *  parameters.
     *
     * @return object Decoded JSON object.
     */
    public function post($endpoint, $params)
    {
        $url = $this->endpointURL . '/' . ltrim($endpoint, '/');
        $header = array("Content-Type: application/xml");

        return $this->request($url, 'POST', $header, $params);
    }

    /**
     * Retrieve a contact.
     *
     * @param id    Contact id.
     *
     * @return On success, return an associative array, where key is contact ID
     *  and key is the array of fields, where key is the field name and value
     *  is the field value.
     *  On error, throw an exception.
     */
    public function getContactById($id)
    {
        if (!isset($id) || empty($id)) {
            throw new \Exception(__FUNCTION__ . ': Invalid contact ID.');
        }

        $contactInfo = $this->get("/contacts/$id", null);

        // Throw exception if $data returns 'failed' status.
        if ($contactInfo->status == 'failed') {
            // TODO: Work out each specific error.
            throw new \Exception(__FUNCTION__ . ': Failed to retrieve the contact.');
        }

        // $data returns 'success' status.
        $contact = array();
        if (is_object($contactInfo) && property_exists($contactInfo, "item")) {
            $id = (string)$contactInfo->item->id;
            $fields = get_object_vars($contactInfo->item->fields);
            $contact[$id] = $fields;
        }

        return $contact;
    }

    /**
     * Retrieve all contacts.
     *
     * @return On success, return an associative array of contacts, where key
     *  is contact ID and key is the array of fields, where key is the
     *  field name and value is the field value.
     *  On error, throw an exception.
     */
    public function getContacts()
    {
        $data = $this->get("/contacts", null);

        // Throw exception if $data returns 'failed' status.
        if ($data->status == 'failed') {
            // TODO: Work out each specific error.
            throw new \Exception(__FUNCTION__ . ': Failed to retrieve all contacts.');
        }

        // $data returns 'success' status.
        $contacts = array();
        foreach ($data as $id => $contact) {
            if (is_object($contact) && property_exists($contact, "id")) {
                $id = (string)$contact->id;
                $contactInfo = $this->getContactById($id, null);
//                $contacts[$id] = $contactInfo[$id];
                $contacts[] = $contactInfo[$id];
            }
        }

        return $contacts;
    }
}
