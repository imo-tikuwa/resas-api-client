<?php
require_once (dirname(__DIR__) . "/vendor/autoload.php");
require_once 'common.php';

use TikuwaApp\Api\ResasApiClient;
use TikuwaApp\Utility\Hash;

// 東京都の市区町村のうち「〇〇区」に一致する自治体のみを絞り込んで取得する
try {
    $resas_api_client = new ResasApiClient();
    $result = $resas_api_client->find('api/v1/cities', ['prefCode' => '13'])
    ->set_kv_path("{n}[cityName=/^.+区$/].cityCode", "{n}[cityName=/^.+区$/].cityName")
    ->to_array();
    logging($result, __FILE__);
} catch(Exception $e) {
    echo $e->getMessage();
}
