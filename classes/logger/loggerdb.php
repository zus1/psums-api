<?php

namespace PsumsApi\Classes\Log;

use Exception;
use Psums\Classes\Factory;
use PsumsApi\Interfaces\LoggerInterface;

/**
 * Class LoggerDb
 * @package PsumsApi\Classes\Log
 *
 * Logger for handling db log driver
 *
 */
class LoggerDb extends Logger implements LoggerInterface
{

    /**
     * @param string $type
     * @return array
     */
    public function getLoggerSettings(string $type): array
    {
        return array(
            self::LOGGER_API => array("model" => Factory::getModel(Factory::MODEL_LOGGER_API)),
            self::LOGGER_DEFAULT => array("model" => Factory::getModel(Factory::MODEL_LOGGER)),
        )[$type];
    }

    /**
     *
     * Returns logger model to use, depending on type
     *
     * @return mixed|void
     */
    private function getModel() {
        $settings = $this->getLoggerSettings($this->type);
        if(empty($settings)) {
            return;
        }
        return $settings["model"];
    }

    /**
     * @param Exception $e
     */
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

    /**
     * @param string $message
     * @param string|null $type
     */
    public function log(string $message, ?string $type = "message"): void {
        $model = $this->getModel();
        $model->insert(array(
            "type" => $type,
            "message" => $message
        ));
    }
}