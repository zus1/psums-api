<?php

namespace PsumsApi\Classes;
use Exception;
use Psums\Classes\Factory;

/**
 * Class Guardian
 * @package PsumsApi\Classes
 *
 * Class for handling security
 * At the moment check api key in api requests
 *
 */
class Guardian
{
    private $request;
    private $authHeader = "Auth";

    public function __construct(Request $request) {
        $this->request = $request;
    }

    private $apiKeyCheckExcludedRoutes = array("/");

    public function getModel() {
        return Factory::getModel(Factory::MODEL_API_KEYS);
    }

    /**
     *
     * Check if api key header exists and validates key
     * Denies access if key not found or invalid
     *
     * @param string $requestUri
     * @throws Exception
     */
    public function checkApiKey(string $requestUri) {
        if(in_array($requestUri, $this->apiKeyCheckExcludedRoutes)) {
            return;
        }
        $requestKey = $this->request->getHeader($this->authHeader, "");
        if(!$requestKey) {
            throw new Exception("Api key missing", HttpCodes::HTTP_BAD_REQUEST);
        }
        $availableKeys = $this->getModel()->select(array("api_key"), array());
        if(!$availableKeys) {
            throw new Exception("No api keys found", HttpCodes::INTERNAL_SERVER_ERROR);
        }
        $keyFound = false;
        array_walk($availableKeys, function ($value) use($requestKey, &$keyFound) {
            if($value["api_key"] === $requestKey) {
                $keyFound = true;
            }
        });

        if($keyFound === false) {
            throw new Exception("Invalid api key", HttpCodes::UNAUTHORIZED);
        }
    }
}