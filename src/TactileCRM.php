<?php

namespace Lengieng\SimplySync;

class TactileCRM
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
     * @param string @serviceURL    Service URL
     * @param string $token         API token
     * @param boolean @secure       Secure connection (https)
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
     * @return string   Service URL
     */
    public function getServiceURL()
    {
        return $this->serviceURL;
    }
    
    /**
     * Set service URL.
     *
     * @param string $servceURL  Service URL
     *
     * @return void
     */
    public function setServiceURL($serviceURL)
    {
        $this->serviceURL = $serviceURL;
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
     * @param mixed $params     An array of key/value strings if method is GET.
     *  A JSON object if the method is POST.
     *
     * @return object Decoded JSON object
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
        
        if (is_array($header) && count($header) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        
        switch ($method) {
            case 'GET':
                $url .= '?' . http_build_query($params);
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
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
     * Perform endpoint request.
     *
     * @param string   $endpoint    Endpoint URL
     * @param string[] $params      The key/value pairs of the request
     *  parameters.
     *
     * @return object   Decoded JSON object
     */
    public function get($endpoint, $params)
    {
        $url = $this->serviceURL . '/' . ltrim($endpoint, '/');
        
        return $this->request($url, 'GET', array(), $params);
    }
    
    /**
     * Get people object by page number.
     *
     * @param integer $pageNum  Page number to return
     * @param integer $limit    Number of results per page to return
     *
     * @return object   Decoded JSON object representing an array of people per
     *  given page number.
     */
    protected function getPeopleByPage($pageNum, $limit = 30)
    {
        $query = array(
            'api_token' => $this->getToken(),
            'limit' => $limit,
            'page' => $pageNum,
        );
        return $this->get('/people', $query);
    }

    /**
     * Get all people.
     *
     * @return object Decoded JSON object representing an array of all people.
     */
    public function getPeople()
    {
        $data = array();
        $cur_page = 1;
        
        do {
            $page = $this->getPeopleByPage($cur_page);
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
}