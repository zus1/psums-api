<?php

class Request
{
    private $requestVars = array();

    public function __construct() {
        $payload = $this->getPayload();
        array_walk($payload, function($value, $key) {
            $this->requestVars[$key] = $value;
        });
    }

    private function getPayload() {
        $payload = $_GET;
        if(empty($payload)) {
            $payload = $_POST;
        }

        return $payload;
    }

    public function __get($name) {
        return $this->requestVars[$name];
    }

    public function input($key, ?string $default="") {
        if(isset($this->requestVars[$key])) {
            return $this->requestVars[$key];
        }

        return $default;
    }

    public function inputOrThrow(string $key, ?int $code=0) {
        if($code === 0) {
            $code = HttpCodes::HTTP_BAD_REQUEST;
        }
        if(!isset($this->requestVars[$key])) {
            throw new Exception("Parameter {$key} is missing", $code);
        }
        if(empty($this->requestVars[$key])) {
            throw new Exception("Parameter {$key} can't be empty", $code);
        }

        return $this->requestVars[$key];
    }

    public function exists(string $key) {
        if(isset($this->requestVars[$key]) && !empty($this->requestVars[$key])) {
            return true;
        }
        return false;
    }

    public function getAll() {
        return $this->requestVars;
    }

    public function getHeaders() {
        return getallheaders();
    }

    public function getHeader(string $key, $default=null) {
        $allHeaders = $this->getHeaders();
        if(array_key_exists($key, $allHeaders)) {
            return $allHeaders[$key];
        }

        return ($default !== null)? $default : "";
    }

    public function getRequestIp() {
        return $_SERVER["REMOTE_ADDR"];
    }

    public function getParsedRequestUrl() {
        return parse_url(strtolower($_SERVER["REQUEST_URI"]));
    }

    public function getParsedRequestQuery(array $output) {
        $parsedUrl = $this->getParsedRequestUrl();
        if(!$parsedUrl || !isset($parsedUrl["query"])) {
            return $output;
        }

         parse_str($parsedUrl["query"], $output);

        return $output;
    }

    public function getRequestPath() {
        $parsedUrl = $this->getParsedRequestUrl();
        if(!$parsedUrl || !isset($parsedUrl["path"])) {
            return "";
        }

        return $parsedUrl["path"];
    }
}