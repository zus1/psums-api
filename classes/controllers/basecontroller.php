<?php


class BaseController
{
    private $response;

    public function __construct(Response $response) {
        $this->response = $response;
    }

    public function webRoot() {
        return $this->response->returnApiOk(array("message" => "Nothing to find here"));
    }
}