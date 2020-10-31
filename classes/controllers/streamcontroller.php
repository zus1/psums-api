<?php


class StreamController
{
    private $validator;
    private $stream;

    public function __construct(Validator $validator, Stream $stream) {
        $this->validator = $validator;
        $this->stream = $stream;
    }

    public function cycleStreams() {
        try {
            /*$session = new SNMP(SNMP::VERSION_2c, "aggregator", 'boguscommunity');
            if($session->getErrno()) {
                throw new Exception($session->getError());
            }*/
            $availableStreams = $this->stream->getModel()->select(array("stream_id", "name"), array());
            $this->stream->doCycleStreams($availableStreams);
        } catch(Exception $e) {
            return $e->getMessage();
        }

        $streamResponses = $this->stream->getStreamResponses();
        $streamResponsesStr = "all on timeout";
        if(!empty($streamResponses)) {
            $streamResponsesStr = implode("\n", $streamResponses) . PHP_EOL;
        }

        return $streamResponsesStr;
    }
}