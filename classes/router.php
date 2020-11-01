<?php

namespace PsumsApi\Classes;
use Exception;
use Psums\Classes\Factory;

/**
 * Class Router
 * @package PsumsApi\Classes
 *
 * Class that handles incoming http requests and routs them to controllers
 * Supports GET and POST
 *
 */
class Router
{
    const REQUEST_POST = 'post';
    const REQUEST_GET = "get";
    private $eHandler;
    private $guardian;

    private $supportedRequestMethods = array(self::REQUEST_GET, self::REQUEST_POST);

    public function __construct(ExceptionHandler $eHandler, Guardian $guardian) {
        $this->eHandler = $eHandler;
        $this->guardian = $guardian;
    }

    public function apiRouteMapping() {
        return array(
            '/' => array('class' => Factory::TYPE_BASE_CONTROLLER, 'method' => 'webRoot', 'request' => self::REQUEST_GET), //in case somebody hits root
            '/report/available/streams' => array('class' => Factory::TYPE_API_CONTROLLER, 'method' => 'availableStreams', 'request' => self::REQUEST_GET),
            '/report/stream/available' => array('class' => Factory::TYPE_API_CONTROLLER, 'method' => 'availableRulesForStream', 'request' => self::REQUEST_GET),
            '/report/stream/report' => array('class' => Factory::TYPE_API_CONTROLLER, 'method' => 'generateReport', 'request' => self::REQUEST_GET),
        );
    }

    /**
     *
     * Takes input request uri, performs validation and routs it to specified controller
     *
     * @throws Exception
     */
    public function routeApi() {
        $requestUri = explode("?", strtolower($_SERVER["REQUEST_URI"]))[0];
        $requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);
        $routes = $this->apiRouteMapping();

        try {
            $route = $this->validateRequest($requestUri, $routes, $requestMethod);
        } catch(Exception $e) {
            $this->eHandler->handle($e);
        }

        $classObject = Factory::getObject($route['class']);
        try {
            $this->validateClassMethod($classObject, $route['method']);
        } catch(Exception $e) {
            $this->eHandler->handle($e);
        }

        try {
            $result = call_user_func([$classObject, $route['method']]);
        } catch(Exception $e) {
            $this->eHandler->handle($e);
        }

        $this->returnResult($result);
    }

    /**
     *
     * Validates input request
     * Checks if method is valid and if method is defined in mapping array
     *
     * @param string $requestUri
     * @param array $routes
     * @param string $requestMethod
     * @return mixed
     * @throws Exception
     */
    private function validateRequest(string $requestUri, array $routes, string $requestMethod) {
        if(!array_key_exists($requestUri, $routes)) {
            throw new Exception("Unknown endpoint", HttpCodes::HTTP_NOT_FOUND);
        }
        $this->guardian->checkApiKey($requestUri);
        $route = $routes[$requestUri];
        if(!in_array($route['request'], $this->supportedRequestMethods)) {
            throw new Exception("Method not supported", HttpCodes::METHOD_NOT_ALLOWED);
        }

        if($requestMethod !== $route['request']) {
            throw new Exception("Method invalid", HttpCodes::HTTP_FORBIDDEN);
        }

        return $route;
    }

    /**
     *
     * Check if method exists on controller
     *
     * @param object $classObject
     * @param string $classMethod
     * @throws Exception
     */
    private function validateClassMethod(object $classObject, string $classMethod) {
        if(!method_exists($classObject, $classMethod)) {
            throw new Exception("Method not found", HttpCodes::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * Returns valid Ok result
     *
     * @param string $result
     */
    private function returnResult(string $result) {
        http_response_code(HttpCodes::HTTP_OK);
        echo $result;
    }

    /**
     *
     * Redirects to specified url.
     * Optionally adds get parameters to url.
     * If timout added, will delay redirect
     *
     * @param string $url
     * @param int|null $code
     * @param array|null $data
     * @param int $timeout
     */
    public function redirect(string $url, ?int $code=null, ?array $data = array(), $timeout=0) {
        $code = ($code)? $code : HttpCodes::HTTP_OK;
        http_response_code($code);
        if(!empty($data)) {
            $httpQuery = http_build_query($data);
            $url = $url . "?" . $httpQuery;
        }
        if($timeout > 0) {
            header(sprintf("refresh:%d;url=%s", $timeout, $url));
        } else  {
            header("Location: " . $url);
        }
        die();
    }
}