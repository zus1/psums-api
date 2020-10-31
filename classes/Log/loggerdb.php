<?php


class LoggerDb extends Logger implements LoggerInterface
{
    public function getLoggerSettings(string $type): array
    {
        return array(
            self::LOGGER_API => array("model" => Factory::getModel(Factory::MODEL_LOGGER_API)),
            self::LOGGER_DEFAULT => array("model" => Factory::getModel(Factory::MODEL_LOGGER)),
        )[$type];
    }

    private function getModel() {
        $settings = $this->getLoggerSettings($this->type);
        if(empty($settings)) {
            return;
        }
        return $settings["model"];
    }

    public function logException(Exception $e): void
    {
        if(in_array($e->getMessage(), $this->excludedExceptions)) {
            return;
        }
        $model = $this->getModel();
        $model->insert(array(
            "type" => "exception",
            'message' => $e->getMessage(),
            'code' => ($e->getCode())? $e->getCode() : null,
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $this->formatExceptionTrace($e)
        ));
    }

    public function log(string $message, ?string $type = "message"): void {
        $model = $this->getModel();
        $model->insert(array(
            "type" => $type,
            "message" => $message
        ));
    }
}