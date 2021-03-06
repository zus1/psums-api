<?php

namespace PsumsApi\Classes\Controllers;
use Exception;
use PsumsApi\Classes\HttpCodes;
use PsumsApi\Classes\Report;
use PsumsApi\Classes\Request;
use PsumsApi\Classes\Response;
use PsumsApi\Classes\Validator;

/**
 * Class ApiController
 * @package PsumsApi\Classes\Controllers
 *
 * Front controller for handling api requests
 *
 */
class ApiController
{
    private $request;
    private $response;
    private $validator;
    private $report;

    public function __construct(Request $request, Response $response, Validator $validator, Report $report) {
        $this->request = $request;
        $this->response = $response;
        $this->validator = $validator;
        $this->report = $report;
    }

    /**
     *
     * Inits available streams generate
     *
     * @return false|string
     */
    public function availableStreams() {
        $available = $this->report->reportAvailableStreams();
        return $this->response->returnApiOk(array("available_streams" => $available));
    }

    /**
     *
     * Inits available rules generate
     *
     * @return false|string
     * @throws Exception
     */
    public function availableRulesForStream() {
        $streamId = $this->request->inputOrThrow("stream_id");
        if($this->validator->validate("stream_id", array(Validator::FILTER_ALPHA_NUM))->isFailed()) {
            throw new Exception($this->validator->getMessages()[0], HttpCodes::HTTP_BAD_REQUEST);
        }
        $availableRules = $this->report->reportRulesForStream($streamId);
        return $this->response->returnApiOk($availableRules);
    }

    /**
     *
     * Inits report generate
     *
     * @return false|string
     * @throws Exception
     */
    public function generateReport() {
        $streamIdOne = $this->request->inputOrThrow("stream_one");
        $streamIdTwo = "";
        $ruleId = 0;
        if($this->validator->validate("stream_one", array(Validator::FILTER_ALPHA_NUM))->isFailed()) {
            throw new Exception($this->validator->getMessages(), HttpCodes::HTTP_BAD_REQUEST);
        }
        $this->validator->resetMessages();
        if($this->request->exists("stream_two")) {
            $streamIdTwo = $this->request->input("stream_two");
            if($this->validator->validate("stream_two", array(Validator::FILTER_ALPHA_NUM))->isFailed()) {
                throw new Exception($this->validator->getMessages()[0], HttpCodes::HTTP_BAD_REQUEST);
            }
            $this->validator->resetMessages();
        }
        if($this->request->exists("rule_id")) {
            $ruleId = $this->request->input("rule_id");
            if($this->validator->validate("rule_id", array(Validator::FILTER_NUMERIC))->isFailed()) {
                throw new Exception($this->validator->getMessages()[0], HttpCodes::HTTP_BAD_REQUEST);
            }
        }

        $report = $this->report->reportGenerateForStreams($streamIdOne, $streamIdTwo, $ruleId);

        return $this->response->returnApiOk($report);
    }
}