<?php


class StreamModel extends Model
{
    protected $idField = 'id';
    protected $table = "stream";
    protected $dataSet = array("id", "stream_id", "stream", "rules", "created_at", "updated_at", "name");
}