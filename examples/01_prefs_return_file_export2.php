<?php
require_once (dirname(__DIR__) . "/vendor/autoload.php");
require_once 'common.php';

use TikuwaApp\Api\ResasApiClient;

// 都道府県の一覧を取得する
try {
    $date = new DateTime();
	$date->setTimezone(new DateTimeZone('Asia/Tokyo'));
    $filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . basename(__FILE__, ".php") . '_' . $date->format('YmdHis') . '.php';

    $api_key = getenv('ENV_RESAS_API_KEY');
    $resas_api_client = new ResasApiClient($api_key);
    $resas_api_client->find('api/v1/prefectures')->export_to($filename, true);

    // 生成したPHPファイルを読み込んでの処理(先頭から10件のデータを取得して配列に書き込み)
    $data = include ($filename);
    $data = array_slice($data, 0, 10);
    logging($data, __FILE__);
} catch(Exception $e) {
    echo $e->getMessage();
}
