<?php

namespace Lengieng\SimplySync;

interface IRestfulConnection
{
    public function isSecureConnection();
    public function setSecureConnection($secure);
    public function request($url, $method, $headers, $params);
    public function get($endpoint, $params);
    public function post($endpoint, $params);
}
