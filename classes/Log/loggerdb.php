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

    public function logApi(string $api, string $rawResult, ?int $error=0, ?int $code=0): void
    {
        $model = $this->getModel();
        $model->insert(array(
            "api" => $api,
            'raw_result' => $rawResult,
            'error' => $error,
            'code' => $code,
        ));
    }

    public function log(string $message, ?string $type = "message"): void {
        $model = $this->getModel();
        $model->insert(array(
            "api" => $type,
            "raw" => $message
        ));
    }
}