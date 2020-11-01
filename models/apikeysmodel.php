<?php

namespace PsumsApi\Models;

class ApiKeysModel extends Model
{
    protected $idField = 'id';
    protected $table = 'api_keys';
    protected $dataSet = array(
        "id", "api_key"
    );
}