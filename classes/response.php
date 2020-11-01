<?php

namespace PsumsApi\Classes;

/**
 * Class Response
 * @package PsumsApi\Classes
 *
 * Main response class.
 * Called in controllers and passes response to router
 *
 */
class Response
{
    /**
     *
     * Returns exception response in json format
     *
     *
     * @param string $message
     * @param int|null $code
     * @return false|string
     */
    public function returnApiException(string $message, ?int $code=0) {
        if($code === 0) {
            $code = HttpCodes::INTERNAL_SERVER_ERROR;
        }
        http_response_code($code);
        $this->addResponseHeaders();
        return json_encode(array("error" => 1, "message" => $message), JSON_UNESCAPED_UNICODE);
    }

    /**
     *
     * Return ok response in json format
     *
     * @param array $payload
     * @return false|string
     */
    public function returnApiOk(array $payload) {
        $base = array("error" => 0);
        $return = array_merge($base, $payload);
        $this->addResponseHeaders();
        return json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    private function addResponseHeaders() {
        header("Content-Type: application/json");
    }
}