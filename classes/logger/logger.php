<?php

namespace PsumsApi\Classes\Log;
use Exception;
use PsumsApi\Classes\HttpCodes;
use PsumsApi\Interfaces\LoggerInterface;

/**
 * Class Logger
 * @package PsumsApi\Classes\Log
 *
 * Main logger for tris service. Will log to db or file depending on child called
 * Used driver can be adjusted true config file (.env or init)
 *
 */
class Logger implements LoggerInterface
{
    const LOGGER_API = "api";
    const LOGGER_DEFAULT = "log";

    protected $type = "log";
    protected $availableTypes = array(self::LOGGER_API);

    protected $excludedExceptions = array("Aggregator on timeout");

    /**
     *
     * Set type for log driver, to use
     *
     * @param string $type
     * @return $this
     * @throws Exception
     */
    public function setType(string $type) {
        if(!in_array($type, $this->availableTypes)) {
            throw new Exception("Logger not supported");
        }
        $this->type = $type;

        return $this;
    }

    /**
     *
     * Returns settings to use for logger, depending on which driver is in use
     *
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function getLoggerSettings(string $type) : array {
        throw new Exception("If you are here, something is wrong", HttpCodes::INTERNAL_SERVER_ERROR); //needs to be overriden in child class
    }

    /**
     * @param Exception $e
     * @throws Exception
     */
    public function logException(Exception $e) : void {
        throw new Exception("If you are here, something is wrong", HttpCodes::INTERNAL_SERVER_ERROR); //needs to be overriden in child class
    }

    /**
     * @param string $message
     * @param string|null $type
     * @throws Exception
     */
    public function log(string $message, ?string $type = "message"): void {
        throw new Exception("If you are here, something is wrong", HttpCodes::INTERNAL_SERVER_ERROR); //needs to be overriden in child class
    }

    /**
     *
     * WE want to have nicely formatted trace for exceptions
     *
     * @param Exception $e
     * @return string
     */
    protected function formatExceptionTrace(Exception $e) {
        $trace = $e->getTraceAsString();
        $trace = explode("#", $trace);
        array_shift($trace);
        $trace = array_map(function($value) {
            return "#" . $value;
        }, $trace);

        return implode("\n", $trace);
    }
}