<?php

class Factory
{
    const TYPE_STREAM_CONTROLLER = "stream-controller";
    const TYPE_DATABASE = "database";
    const TYPE_ROUTER = "router";
    const TYPE_HTTP_PARSER = "httpparser";
    const TYPE_VALIDATOR = 'validator';
    const TYPE_JSON_PARSER = "json-parser";
    const TYPE_DATE_HANDLER = "date-handler";
    const TYPE_STREAM = "stream";
    const TYPE_SIGN = "sign";

    const MODEL_LOGGER_WEB = "model-logger-web";
    const MODEL_LOGGER_API = "model-logger-api";
    const MODEL_LOGGER_STREAM = "model-logger-stream";
    const MODEL_LOGGER = "model-logger-default";
    const MODEL_STREAM = "model-stream";
    const MODEL_SIGN = "model-sign";
    const MODEL_STREAM_INPUT = "model-stream-input";

    const LOGGER_FILE = 'file';
    const LOGGER_DB = "db";

    const API_FAST = "api-fast";
    const API_BACON = "api-bacon";
    const API_HIPSUM = "api-hipsum";
    const API_META = "api-meta";
    const API_AGGREGATOR = "aggregator";

    const TYPE_METHOD_MAPPING = array(
        self::TYPE_DATABASE => "getDatabase",
        self::TYPE_HTTP_PARSER => "getHttpParser",
        self::TYPE_VALIDATOR => 'getValidator',
        self::TYPE_JSON_PARSER => "getJsonParser",
        self::TYPE_DATE_HANDLER => "getDateHandler",
        self::TYPE_STREAM_CONTROLLER => "getStreamController",
        self::TYPE_STREAM => "getStream",
        self::TYPE_SIGN => "getSign",
    );

    const MODEL_TO_METHOD_MAPPING = array(
        self::MODEL_LOGGER_WEB => "getModelLoggerWeb",
        self::MODEL_LOGGER_API => "getModelLoggerApi",
        self::MODEL_LOGGER_STREAM => "getModelLoggerStream",
        self::MODEL_LOGGER => "getModelLogger",
        self::MODEL_STREAM => "getModelStream",
        self::MODEL_SIGN => "getModelSign",
        self::MODEL_STREAM_INPUT => "getModelStreamInput",
    );

    const API_TO_MODEL_MAPPING = array(
        self::API_FAST => "getApiFast",
        self::API_BACON => "getApiBacon",
        self::API_HIPSUM => "getApiHipsum",
        self::API_META => "getApiMeta",
        self::API_AGGREGATOR => "getApiAggregator",
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
     * @return Database|Validator|DateHandler|Stream|StreamController
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
     * @return StreamInputModel
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
     * @param string $apiType
     * @return CallMetaphorpsum|CallHipsum|CallBaconipsum|CallAsdfast|CallAggregator|null
     */
    public static function getApi(string $apiType) {
        if(!array_key_exists($apiType, self::API_TO_MODEL_MAPPING)) {
            return null;
        }
        if(!isset(self::$instances[$apiType])) {
            $object = call_user_func([new self(), self::API_TO_MODEL_MAPPING[$apiType]]);
            self::$instances[$apiType] = $object;
        }

        return self::$instances[$apiType];
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

    private function getModelStreamInput() {
        return new StreamInputModel($this->getValidator());
    }

    private function getApiAggregator() {
        return new CallAggregator(self::getLogger());
    }

    private function getApiFast() {
        return new CallAsdfast(self::getLogger());
    }

    private function getApiBacon() {
        return new CallBaconipsum(self::getLogger());
    }

    private function getApiHipsum() {
        return new CallHipsum(self::getLogger());
    }

    private function getApiMeta() {
        return new CallMetaphorpsum(self::getLogger());
    }

    private function getSign() {
        return new Sign();
    }

    private function getModelSign() {
        return new SignModel($this->getValidator());
    }

    private function getStream() {
        return new Stream($this->getValidator(), self::getLogger(), $this->getSign());
    }

    private function getModelStream() {
        return new StreamModel($this->getValidator());
    }

    private function getStreamController() {
        return new StreamController($this->getValidator(), $this->getStream());
    }

    private function getExceptionHandler() {
        return new ExceptionHandler(self::getLogger());
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
        return new Validator();
    }

    private function getJsonParser() {
        return new JsonParser();
    }

    public function getDateHandler() {
        return new DateHandler();
    }
}