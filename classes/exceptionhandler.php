<?php


class ExceptionHandler
{
    const EXCEPTION_DEFAULT = "default";

    private $logger;
    private $response;

    public function __construct(LoggerInterface $logger, Response $response) {
        $this->logger = $logger;
        $this->response = $response;
    }

    private function getTypeTOHandlerMapping() {
        return array(
            self::EXCEPTION_DEFAULT => "handleException",
        );
    }

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

    private function handleException(Exception $e) {
        $this->logger->setType(Logger::LOGGER_API)->logException($e);
        echo $this->response->returnApiException($e->getMessage(), $e->getCode());
        die();
    }
}