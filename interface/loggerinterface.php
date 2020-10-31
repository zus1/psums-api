<?php


interface LoggerInterface
{
    public function getLoggerSettings(string $type) : array;

    public function logApi(string $api, string $rawResult, ?int $error=0, ?int $code=0) : void;

    public function log(string $message, ?string $type="message") : void;
}