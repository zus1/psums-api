<?php

namespace PsumsApi\Models;

class LoggerModel extends Model
{
    protected $idField = 'id';
    protected $table = 'log';
    protected $dataSet = array(
        "id", "type", "message", "code", "file", "line", "created_at", "trace"
    );
}