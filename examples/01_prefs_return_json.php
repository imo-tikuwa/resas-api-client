<?php
require_once (dirname(__DIR__) . "/vendor/autoload.php");
require_once 'common.php';

use TikuwaApp\Api\ResasApiClient;

// 都道府県の一覧を取得する
try {
    $resas_api_client = new ResasApiClient();
    $result = $resas_api_client->find('api/v1/prefectures')->to_json();
    logging($result, __FILE__);
} catch(Exception $e) {
    echo $e->getMessage();
}
