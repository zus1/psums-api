<?php

namespace PsumsApi\Classes\Controllers;

use PsumsApi\Classes\Response;

/**
 * Class BaseController
 * @package PsumsApi\Classes\Controllers
 *
 * Front controller for handing non api related requests
 * At the moment handles only possible root call
 *
 */
class BaseController
{
    private $response;

    public function __construct(Response $response) {
        $this->response = $response;
    }

    /**
     *
     * Returns default response if somebody hits root
     *
     * @return false|string
     */
    public function webRoot() {
        return $this->response->returnApiOk(array("message" => "Nothing to find here"));
    }
}