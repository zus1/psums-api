<?php


class LoggerApiModel extends Model
{
    protected $idField = 'id';
    protected $table = 'streams_api_call_log';
    protected $dataSet = array(
        "id", "api", "raw_result", "error", "code", "last_updated"
    );
}