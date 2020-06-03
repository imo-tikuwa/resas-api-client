<?php
require_once (dirname(__DIR__) . "/vendor/autoload.php");
require_once 'common.php';

use TikuwaApp\Api\ResasApiClient;

// 都道府県の一覧を取得する
try {
    $api_key = getenv('ENV_RESAS_API_KEY');
    $resas_api_client = new ResasApiClient($api_key);
    $result = $resas_api_client->find('api/v1/prefectures')->to_array();
    logging($result, __FILE__);
} catch(Exception $e) {
    echo $e->getMessage();
}
