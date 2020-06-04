<?php
require_once (dirname(__DIR__) . "/vendor/autoload.php");
require_once 'common.php';

use TikuwaApp\Api\ResasApiClient;

// 東京都の市区町村一覧を取得する
try {
    $resas_api_client = new ResasApiClient();
    $result = $resas_api_client->find('api/v1/cities', ['prefCode' => '13'])->to_array();
    logging($result, __FILE__);
} catch(Exception $e) {
    echo $e->getMessage();
}
