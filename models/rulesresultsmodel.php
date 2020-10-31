<?php


class RulesResultsModel extends Model
{
    protected $idField = 'id';
    protected $table = 'rules_results';
    protected $dataSet = array(
        "id", "first_stream", "second_stream", "rule_name", "rule_id", "results"
    );
}