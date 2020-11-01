<?php

namespace PsumsApi\Classes;

use Psums\Classes\Factory;
use Exception;

/**
 * Class Report
 * @package PsumsApi\Classes
 *
 * Class that generates reports for results of applied stream rules on other services
 *
 */
class Report
{
    private $stream;
    private $rulesResult;

    public function __construct(Stream $stream, RulesResult $result) {
        $this->stream = $stream;
        $this->rulesResult = $result;
    }

    /**
     *
     * Genrates json string of currently available streams
     *
     * @return array|mixed
     * @throws Exception
     */
    public function reportAvailableStreams() {
        $allStreams = $this->stream->getStreamModel()->select(array("stream_id", "name"), array());
        if(!$allStreams) {
            return array();
        }

        return $allStreams;
    }

    /**
     *
     * Generates json string containing all available rules for requested stream
     *
     * @param string $streamId
     * @return array
     */
    public function reportRulesForStream(string $streamId) {
        $report = array(
            'stream_id' => $streamId,
            'available_rules' => array()
        );
        $rules = Factory::getObject(Factory::TYPE_DATABASE)->select(
            "SELECT t1.second_stream, t1.rule_id, t2.rule_name, t2.rule_description FROM stream_rules as t1 INNER JOIN rules_available as t2 ON t1.rule_id = t2.id WHERE first_stream = ?",
            array("string"), array($streamId));
        if(!$rules) {
            return $report;
        }
        array_walk($rules, function ($value) use(&$report) {
            $report['available_rules'][] = array(
                'stream_id' => $value["second_stream"],
                'rule_id' => $value["rule_id"],
                "rule_name" => $value["rule_name"],
                'description' => $value["rule_description"]
            );
        });

        return $report;
    }

    /**
     *
     * Generates report for requested stream, second stream that rule is checked in combination with first stream and rule id for rule  to check
     * If only stream one supplied, will return all available results for that stream
     * If only stream one and stream two supplied will return all results for those two streams (all available rules)
     *
     * @param string $streamIdOne
     * @param string|null $streamIdTwo
     * @param int|null $ruleId
     * @return array
     * @throws Exception
     */
    public function reportGenerateForStreams(string $streamIdOne, ?string $streamIdTwo="", ?int $ruleId=0) {
        $whereArray = array("first_stream" => $streamIdOne);
        if($streamIdTwo !== "") {
            $whereArray["second_stream"] =$streamIdTwo;
        }
        if($ruleId !== 0) {
            $whereArray["rule_id"] = $ruleId;
        }
        $report = $this->getBaseReport($streamIdOne);
        $results = $this->rulesResult->getModel()->select(array("second_stream", "rule_id", "rule_name", "results"), $whereArray);
        if(!$results) {
            return $report;
        }
        array_walk($results, function ($result) use(&$report) {
            $r = $this->getResultsFromQueryResponse($result);
            $report['results'][] = $this->makeReturnReportArray($result, $r);
        });
        return $report;
    }

    /**
     * @param string $streamIdOne
     * @return array
     */
    private function getBaseReport(string $streamIdOne) {
        return array(
            'stream_id' => $streamIdOne,
            "results" => array()
        );
    }

    /**
     *
     * Returns empty array if results field in db is empty
     * Should not happen, but just in case lets check
     *
     * @param array $queryResponse
     * @return array|mixed
     */
    private function getResultsFromQueryResponse(array $queryResponse) {
        if(empty($queryResponse["results"])) {
            return array();
        }

        return json_decode($queryResponse["results"], true);
    }

    /**
     * @param array $queryResponse
     * @param array $queryResults
     * @return array
     */
    private function makeReturnReportArray(array $queryResponse, array $queryResults) {
        return array(
            'stream_id' => $queryResponse["second_stream"],
            'rule' => $queryResponse["rule_name"],
            'rule_id' => $queryResponse["rule_id"],
            'report' => $queryResults
        );
    }
}