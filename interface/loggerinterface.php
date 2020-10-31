<?php


interface LoggerInterface
{
    public function getLoggerSettings(string $type) : array;

    public function logException(Exception $e) : void;

    public function log(string $message, ?string $type="message") : void;
}