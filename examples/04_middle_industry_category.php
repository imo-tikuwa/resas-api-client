<?php
require_once (dirname(__DIR__) . "/vendor/autoload.php");
require_once 'common.php';

use TikuwaApp\Api\ResasApiClient;

// 情報通信業の産業中分類を取得
try {
    $resas_api_client = new ResasApiClient();
    $result = $resas_api_client->find('api/v1/industries/middle', ['sicCode' => 'G'])->toArray();
    logging($result, __FILE__);
} catch(Exception $e) {
    echo $e->getMessage();
}
