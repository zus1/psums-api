<?php


class SignModel  extends Model
{
    protected $idField = 'id';
    protected $table = "sign";
    protected $dataSet = array("id", "stream_id", "sign_key");
}