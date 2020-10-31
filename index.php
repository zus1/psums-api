<?php
include_once("include.php");
/*$api = Factory::getApi(Factory::API_AGGREGATOR);
$params = array("id" => "1a2b3c4d", 'sign' => "14rg7-7un89-da234-ng5p", "stream" => "lorem ipsum dipsum, tipsum..");
$res = $api->sendToAggregator($params);
var_dump($res);
die();*/
$cnt = Factory::getObject(Factory::TYPE_STREAM_CONTROLLER);
$res = $cnt->cycleStreams();
var_dump($res);
die();
echo "Nothing here";