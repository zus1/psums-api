<?php


class StreamInputModel extends Model
{
    protected $idField = 'id';
    protected $table = "stream_input";
    protected $dataSet = array("id", "name", "stream_id", "input");
}