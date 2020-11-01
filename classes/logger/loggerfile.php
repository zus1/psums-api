<?php

namespace PsumsApi\Classes\Log;
use Exception;
use PsumsApi\Classes\HttpParser;
use PsumsApi\Interfaces\LoggerInterface;

/**
 * Class LoggerFile
 * @package PsumsApi\Classes\Log
 *
 * Logger class for handling file log driver
 *
 */
class LoggerFile extends Logger implements LoggerInterface
{
    private $rootDirectory;

    public function __construct()
    {
        $this->rootDirectory = HttpParser::root() . "/logs/";
    }

    /**
     * @param string $type
     * @return array
     */
    public function getLoggerSettings(string $type): array
    {
        return array(
            self::LOGGER_DEFAULT => array("file" => $this->rootDirectory . "log.log"),
        )[$type];
    }

    /**
     * Creates directory for logs, if it dose not exists
     * Handles ownership of new directory
     */
    private function createLogDirectory() {
        if(!file_exists($this->rootDirectory)) {
            mkdir($this->rootDirectory, 0777);
            $owner = posix_getpwuid(fileowner($this->rootDirectory))["name"];
            $iAm = shell_exec("whoami");
            if($owner !== "www-data" && $iAm === "root") {
                chown($this->rootDirectory, "www-data");
            }
        }
    }

    /**
     * @param string $line
     * @throws Exception
     */
    private function addLine(string $line) {
        $setting = $this->getLoggerSettings($this->type);
        if(!$setting) {
            return;
        }
        $fh = fopen($setting["file"], "a+");
        if(!$fh) {
            throw new Exception("Could not open log file");
        };
        fwrite($fh, $line);
        fclose($fh);
    }

    /**
     * @param Exception $e
     * @throws Exception
     */
    public function logException(Exception $e): void
    {
        if(in_array($e->getMessage(), $this->excludedExceptions)) {
            return;
        }
        $this->createLogDirectory();
        $this->addLine($this->createLogExceptionLine($e));
    }

    /**
     * @param string $message
     * @param string|null $type
     * @throws Exception
     */
    public function log(string $message, ?string $type = "message"): void {
        $this->createLogDirectory();
        $this->addLine($this->createLogMessageLine($message, $type));
    }

    /**
     * @param string $message
     * @param string $type
     * @return string
     */
    private function createLogMessageLine(string $message, string $type) {
        return sprintf("[%s][%s]%s", $type, date("Y-m-d H:i:s"), $message);
    }

    /**
     *
     * Generates single Exception line to be added to log
     *
     * @param Exception $e
     * @return string
     */
    private function createLogExceptionLine(Exception $e) {
        return sprintf("[EXCEPTION][%s]%s(%d)\n%s(%s)\n%s\n\n", date("Y-m-d H:i:s"), $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $this->formatExceptionTrace($e));
    }
}