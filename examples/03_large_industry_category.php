<?php
require_once (dirname(__DIR__) . "/vendor/autoload.php");
require_once 'common.php';

use TikuwaApp\Api\ResasApiClient;

// 産業大分類を取得
try {
    $resas_api_client = new ResasApiClient();
    $result = $resas_api_client->find('api/v1/industries/broad')->toArray();
    logging($result, __FILE__);
} catch(Exception $e) {
    echo $e->getMessage();
}
