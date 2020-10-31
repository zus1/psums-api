<?php

class Response
{
    public function returnApiException(string $message, ?int $code=0) {
        if($code === 0) {
            $code = HttpCodes::INTERNAL_SERVER_ERROR;
        }
        http_response_code($code);
        $this->addResponseHeaders();
        return json_encode(array("error" => 1, "message" => $message), JSON_UNESCAPED_UNICODE);
    }

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