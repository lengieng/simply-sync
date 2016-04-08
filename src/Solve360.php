<?php

namespace Lengieng\SimplySync;

class Solve360
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
    
    public function __construct($email, $token, $secure = true)
    {
        $this->email = $email;
        $this->token = $token;
        $this->secure = $secure;
    }
    
    /**
     * Get user email.
     *
     * @return string   User email
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Set user email used for login.
     *
     * @param string $email     User email
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
     * @return string   User API token
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * Set user API token.
     *
     * @param string $token  User API token
     *
     * @return void
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
    
    /**
     * Check if secure connection is required.
     *
     * @return boolean  true if secure is set
     */
    public function isSecureConnection()
    {
        return $this->secure;
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
        if ($this->isSecureConnection()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        }
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
     * @param string    $endpoint    Endpoint URL
     *
     * @return object Decoded JSON object
     */
    public function get($endpoint)
    {
        $url = $this->endpointURL . '/' . ltrim($endpoint, '/');
        
        $credential = $this->email . ":" . $this->token;
        
        // By default, Solve360 returns XML. We want JSON here.
        $header = array(
            "Authorization: Basic " . base64_encode($credential),
            "Accept: application/json"
        );
        
        return $this->request($url, 'GET', $header, null);
    }
    
    /**
     * Retrieve a contact
     *
     * @param id    Contact id.
     *
     * @return On success, return an associative array, where key is contact ID
     *  and key is the array of fields, where key is the field name and value
     *  is the field value.
     *  On error, throw an exception.
     */
    public function getContactById($id = null)
    {
        if (!isset($id) || empty($id)) {
            throw new \Exception(__FUNCTION__ . ': Invalid contact ID.');
        }
        
        $contactInfo = $this->get("/contacts/$id");

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
     * Retrieve all contacts
     *
     * @return On success, return an associative array of contacts, where key
     *  is contact ID and key is the array of fields, where key is the
     *  field name and value is the field value.
     *  On error, throw an exception.
     */
    public function getContacts()
    {
        $data = $this->get("/contacts");
        
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
                $contactInfo = $this->getContactById($id);
                $contacts[$id] = $contactInfo[$id];
            }
        }
        
        return $contacts;
    }
}
