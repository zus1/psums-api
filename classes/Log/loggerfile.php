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
            self::LOGGER_API => array("file" => $this->rootDirectory . "api_call.log"),
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

    public function logApi(string $api, string $rawResult, ?int $error=0, ?int $code=0): void
    {
        $this->createLogDirectory();
        $this->addLine($this->createLogExceptionLine($api, $rawResult, $error, $code));
    }

    public function log(string $message, ?string $type = "message"): void {
        $this->createLogDirectory();
        $this->addLine($this->createLogMessageLine($message, $type));
    }

    private function createLogMessageLine(string $message, string $type) {
        return sprintf("[%s][%s]%s", $type, date("Y-m-d H:i:s"), $message);
    }

    private function createLogExceptionLine($api, $rawResult, $error, $code) {
        return sprintf("[API_CALL][%s]%s (%s:%d)\n%s", date("Y-m-d H:i:s"), $api, $error, $code, $rawResult);
    }
}