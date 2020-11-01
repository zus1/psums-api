<?php

namespace Psums\Classes;

use PsumsApi\Classes\Controllers\ApiController;
use PsumsApi\Classes\Controllers\BaseController;
use PsumsApi\Classes\Database;
use PsumsApi\Classes\ExceptionHandler;
use PsumsApi\Classes\Guardian;
use PsumsApi\Classes\HttpParser;
use PsumsApi\Classes\Log\LoggerDb;
use PsumsApi\Classes\Log\LoggerFile;
use PsumsApi\Classes\Report;
use PsumsApi\Classes\Request;
use PsumsApi\Classes\Response;
use PsumsApi\Classes\Router;
use PsumsApi\Classes\RulesResult;
use PsumsApi\Classes\Stream;
use PsumsApi\Classes\Validator;
use PsumsApi\Config\Config;
use PsumsApi\Models\ApiKeysModel;
use PsumsApi\Models\LoggerApiModel;
use PsumsApi\Models\LoggerModel;
use PsumsApi\Models\RulesResultsModel;
use PsumsApi\Models\StreamInputModel;
use PsumsApi\Models\StreamModel;

/**
 * Class Factory
 * @package PsumsApi\Classes
 *
 * Main container for generating object and handling dependency injection.
 * Can return Objects, Extenders, Models and Libraries
 *
 */
class Factory
{
    const TYPE_DATABASE = "database";
    const TYPE_ROUTER = "router";
    const TYPE_HTTP_PARSER = "httpparser";
    const TYPE_VALIDATOR = 'validator';
    const TYPE_SIGN = "sign";
    const TYPE_BASE_CONTROLLER = "base-controller";
    const TYPE_REQUEST = "request";
    const TYPE_RESPONSE = "response";
    const TYPE_API_CONTROLLER = "api-controller";
    const TYPE_REPORT = "report";
    const TYPE_STREAM = "stream";
    const TYPE_GUARDIAN = "guardian";
    const TYPE_RULES_RESULT = "rules-result";

    const MODEL_LOGGER_API = "model-logger-api";
    const MODEL_LOGGER = "model-logger-default";
    const MODEL_STREAM = "model-stream";
    const MODEL_STREAM_INPUT = "model-stream-input";
    const MODEL_API_KEYS = "model-api-keys";
    const MODEL_RULES_RESULT = "model-rules-result";

    const LOGGER_FILE = 'file';
    const LOGGER_DB = "db";

    const TYPE_METHOD_MAPPING = array(
        self::TYPE_DATABASE => "getDatabase",
        self::TYPE_HTTP_PARSER => "getHttpParser",
        self::TYPE_VALIDATOR => 'getValidator',
        self::TYPE_SIGN => "getSign",
        self::TYPE_ROUTER => "getRouter",
        self::TYPE_BASE_CONTROLLER => "getBaseController",
        self::TYPE_REQUEST => "getRequest",
        self::TYPE_RESPONSE => "getResponse",
        self::TYPE_API_CONTROLLER => "getApiController",
        self::TYPE_REPORT => "getReport",
        self::TYPE_STREAM => "getStream",
        self::TYPE_GUARDIAN => "getGuardian",
        self::TYPE_RULES_RESULT => "getRulesResult",
    );

    const MODEL_TO_METHOD_MAPPING = array(
        self::MODEL_LOGGER_API => "getModelLoggerApi",
        self::MODEL_LOGGER => "getModelLogger",
        self::MODEL_STREAM => "getModelStream",
        self::MODEL_STREAM_INPUT => "getModelStreamInput",
        self::MODEL_API_KEYS => "getModelApiKeys",
        self::MODEL_RULES_RESULT => "getModelRulesResult",
    );

    const LIBRARY_TO_TYPE_MAPPING = array();

    const LOGGER_TO_METHOD_MAPPING = array(
        self::LOGGER_DB => "getDbLogger",
        self::LOGGER_FILE => "getFileLogger",
    );
    private static $instances = array();

    /**
     * @param string|null $type
     * @return LoggerFile|LoggerDb
     */
    public static function getLogger(?string $type="") {
        if($type === "") {
            $type = Config::get(Config::LOG_DRIVER);
        }
        if(!array_key_exists($type, self::LOGGER_TO_METHOD_MAPPING)) {
            return null;
        }
        if(!array_key_exists($type, self::$instances)) {
            $logger = call_user_func([new self(), self::LOGGER_TO_METHOD_MAPPING[$type]]);
            self::$instances[$type] = $logger;
        }

        return self::$instances[$type];
    }

    /**
     * @param string $type
     * @param bool $singleton
     * @return Database|Validator|Router|Report|Stream|Guardian|RulesResult
     */
    public static function getObject(string $type, bool $singleton=false) {
        if(!array_key_exists($type, self::TYPE_METHOD_MAPPING)) {
            return null;
        }
        if($singleton === true) {
            if(array_key_exists($type, self::$instances)) {
                return self::$instances[$type];
            } else {
                $object = call_user_func([new self(), self::TYPE_METHOD_MAPPING[$type]]);
                self::$instances[$type] = $object;
                return $object;
            }
        }

        return call_user_func([new self(), self::TYPE_METHOD_MAPPING[$type]]);
    }


    /**
     * @param string $modelType
     * @return StreamInputModel|LoggerApiModel|RulesResultsModel
     */
    public static function getModel(string $modelType) {
        if(!array_key_exists($modelType, self::MODEL_TO_METHOD_MAPPING)) {
            return null;
        }
        if(!isset(self::$instances[$modelType])) {
            $object = call_user_func([new self(), self::MODEL_TO_METHOD_MAPPING[$modelType]]);
            self::$instances[$modelType] = $object;
        }

        return self::$instances[$modelType];
    }

    /**
     * @param string $libraryType
     * @return object
     */
    public static function getLibrary(string $libraryType) {
        if(!array_key_exists($libraryType, self::LIBRARY_TO_TYPE_MAPPING)) {
            return null;
        }

        return call_user_func([new self(), self::LIBRARY_TO_TYPE_MAPPING[$libraryType]]);
    }

    private function getModelRulesResult() {
        return new RulesResultsModel($this->getValidator());
    }

    private function getRulesResult() {
        return new RulesResult();
    }

    private function getModelApiKeys() {
        return new ApiKeysModel($this->getValidator());
    }

    private function getGuardian() {
        return new Guardian($this->getRequest());
    }

    private function getStream() {
        return new Stream();
    }

    private function getReport() {
        return new Report($this->getStream(), $this->getRulesResult());
    }

    private function getApiController() {
        return new ApiController($this->getRequest(), $this->getResponse(), $this->getValidator(), $this->getReport());
    }

    private function getRequest() {
        return new Request();
    }

    private function getResponse() {
        return new Response();
    }

    private function getBaseController() {
        return new BaseController($this->getResponse());
    }

    private function getRouter() {
        return new Router($this->getExceptionHandler(), $this->getGuardian());
    }

    private function getModelStreamInput() {
        return new StreamInputModel($this->getValidator());
    }

    private function getModelStream() {
        return new StreamModel($this->getValidator());
    }

    private function getExceptionHandler() {
        return new ExceptionHandler(self::getLogger(), $this->getResponse());
    }

    private function getDbLogger() {
        return new LoggerDb();
    }

    private function getFileLogger() {
        return new LoggerFile();
    }

    private function getModelLoggerApi() {
        return new LoggerApiModel($this->getValidator());
    }

    private function getModelLogger() {
        return new LoggerModel($this->getValidator());
    }

    private function getDatabase() {
        return new Database();
    }

    private function getHttpParser() {
        return new HttpParser();
    }

    private function getValidator() {
        return new Validator($this->getRequest());
    }
}