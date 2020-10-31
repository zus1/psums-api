<?php

class Config
{
    const ADMIN_HOME = 'ADMIN_HOME';
    const USER_HOME = 'USER_HOME';
    const DB_USERNAME = 'DB_USERNAME';
    const DB_PASSWORD = 'DB_PASSWORD';
    const DB_NAME = 'DB_NAME';
    const DB_HOST = 'DB_HOST';
    const DB_CHARSET = 'DB_CHARSET';
    const DB_CONNECTION = "DB_CONNECTION";
    const DB_PORT = "DB_PORT";
    const LOG_DRIVER = "LOG_DRIVER";
    const STREAM_CHUNK_SIZE = "STREAM_CHUNK_SIZE";

    private static $_initialized = false;
    private static $_configArray = array();
    private static $_typeInit = "ini";
    private static $_typeEnv = "env";

    private static function getAvailableConfigFileTypes()  {
        return array(self::$_typeInit, self::$_typeEnv);
    }

    private static function getFileTypeToMethodMapping() {
        return array(
            self::$_typeEnv => "loadConfigFromEnv",
            self::$_typeInit => "loadConfigFromIni",
        );
    }

    public static function init(?string $configFile="") {
        list($configFile, $extension) = self::getConfigFile($configFile);

        $configArray = call_user_func_array(["Config", self::getFileTypeToMethodMapping()[$extension]], array($configFile));
        $extraConfig = self::getExtraConfig();

        self::$_configArray = array_merge($configArray, $extraConfig);
        self::$_initialized = true;
    }

    private static function getConfigFile(string $configFile) {
        $extension = "";
        if($configFile !== "") {
            if(!file_exists($configFile)) {
                throw new Exception("Config file not found");
            }
            $extension = explode(".", $configFile)[1];
            if(!in_array($extension, self::getAvailableConfigFileTypes())) {
                throw new Exception("Unsupported config file type");
            }
        } else {
            foreach(self::getAvailableConfigFileTypes() as $fileType) {
                if($fileType === "env") {
                    $fullPath = HttpParser::root() . "/." . $fileType;
                } else {
                    $fullPath = HttpParser::root() . "/init." . $fileType;
                }
                if(file_exists($fullPath)) {
                    $extension = $fileType;
                    $configFile = $fullPath;
                    break;
                }
            }
        }
        if($configFile === "") {
            throw new Exception("Unsupported config file type");
        }

        return array($configFile, $extension);
    }

    private static function loadConfigFromIni(string $iniFile) {
        $initVariables = parse_ini_file($iniFile);

        $iniConfig = array();
        foreach($initVariables as $key => $value) {
            $iniConfig[$key] = $value;
        }

        return $iniConfig;
    }

    private static function loadConfigFromEnv(string $envFile) {
        $envConfig = array();
        $envContents = file_get_contents($envFile);
        if($envContents && $envContents !== "") {
            $envContentsArray = preg_split("/\n|\r\n/", $envContents);
            array_walk($envContentsArray, function($value) use(&$envConfig) {
                $value = trim($value);
                if($value !== "") {
                    if(!strpos($value, "=")) {
                        throw new Exception("Env file malformed");
                    }
                    $envLineParts = explode("=", $value);
                    if(count($envLineParts) !== 2) {
                        throw new Exception("Env file malformed");
                    }
                    $envConfig[$envLineParts[0]] = (is_int($envLineParts[1]))? (int)$envLineParts[1] : $envLineParts[1];
                }
            });
        }

        return $envConfig;
    }

    public static function getExtraConfig() {
        return array(
            "ADMIN_HOME" => HttpParser::baseUrl() . "views/adm/home.php",
            "USER_HOME" => HttpParser::baseUrl() . "views/documentation.php",
        );
    }

    private static function setConfig(string $key, $value) {
        self::$_configArray[$key] = $value;
    }

    public static function get(string $key, $default=null) {
        if(self::$_initialized === false) {
            self::init();
        }
        if(isset(self::$_configArray[$key]) && !empty(self::$_configArray[$key])) {
            return self::$_configArray[$key];
        }

        return $default;
    }
}