<?php

namespace PsumsApi\Models;

class LoggerApiModel extends Model
{
    protected $idField = 'id';
    protected $table = 'log_api';
    protected $dataSet = array(
        "id", "type", "message", "code", "file", "line", "created_at", "trace"
    );
}