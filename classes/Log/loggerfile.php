<?php


class LoggerFile extends Logger implements LoggerInterface
{
    private $rootDirectory;

    public function __construct()
    {
        $this->rootDirectory = HttpParser::root() . "/logs/";
    }

    public function getLoggerSettings(string $type): array
    {
        return array(
            self::LOGGER_DEFAULT => array("file" => $this->rootDirectory . "log.log"),
        )[$type];
    }

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

    public function logException(Exception $e): void
    {
        if(in_array($e->getMessage(), $this->excludedExceptions)) {
            return;
        }
        $this->createLogDirectory();
        $this->addLine($this->createLogExceptionLine($e));
    }

    public function log(string $message, ?string $type = "message"): void {
        $this->createLogDirectory();
        $this->addLine($this->createLogMessageLine($message, $type));
    }

    private function createLogMessageLine(string $message, string $type) {
        return sprintf("[%s][%s]%s", $type, date("Y-m-d H:i:s"), $message);
    }

    private function createLogExceptionLine(Exception $e) {
        return sprintf("[EXCEPTION][%s]%s(%d)\n%s(%s)\n%s\n\n", date("Y-m-d H:i:s"), $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $this->formatExceptionTrace($e));
    }
}