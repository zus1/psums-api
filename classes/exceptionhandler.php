<?php

namespace PsumsApi\Classes;
use Exception;
use PsumsApi\Classes\Log\Logger;
use PsumsApi\Interfaces\LoggerInterface;

/**
 * Class ExceptionHandler
 * @package PsumsApi\Classes
 *
 * Class for handling project exceptions.
 * Can be extended by using PsumsApi\Extenders\ExceptionHandlerExtender
 *
 */
class ExceptionHandler
{
    const EXCEPTION_DEFAULT = "default";

    private $logger;
    private $response;

    public function __construct(LoggerInterface $logger, Response $response) {
        $this->logger = $logger;
        $this->response = $response;
    }

    /**
     *
     * Return method to use, depending on exception type
     *
     * @return array
     */
    private function getTypeTOHandlerMapping() {
        return array(
            self::EXCEPTION_DEFAULT => "handleException",
        );
    }

    /**
     *
     * Calls handling method depending on type parameter
     *
     * @param Exception $e
     * @param string|null $type
     * @param bool $return
     * @return mixed|null
     * @throws Exception
     */
    public function handle(Exception $e, ?string $type="", $return=false) {
        if($type === "") {
            $type = self::EXCEPTION_DEFAULT;
        }
        if(!array_key_exists($type, $this->getTypeTOHandlerMapping())) {
            $this->logger->logException($e);
            throw $e;
        }

        $method = $this->getTypeTOHandlerMapping()[$type];
        $ret = call_user_func_array([$this, $method], array($e));

        if($return === true) {
            return $ret;
        }
        return null;
    }

    /**
     *
     * Default fallback if no type supplied
     *
     * @param Exception $e
     */
    private function handleException(Exception $e) {
        $this->logger->setType(Logger::LOGGER_API)->logException($e);
        echo $this->response->returnApiException($e->getMessage(), $e->getCode());
        die();
    }
}