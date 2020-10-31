<?php


class LoggerModel extends Model
{
    protected $idField = 'id';
    protected $table = 'streams_log';
    protected $dataSet = array(
        "id", "api", "raw_result", "error", "code", "last_updated"
    );
}