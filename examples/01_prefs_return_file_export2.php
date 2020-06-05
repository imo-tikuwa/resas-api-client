<?php
require_once (dirname(__DIR__) . "/vendor/autoload.php");
require_once 'common.php';

use TikuwaApp\Api\ResasApiClient;

// 都道府県のコード値が15以下のKeyValueペアを取得する
try {
    $date = new DateTime();
    $date->setTimezone(new DateTimeZone('Asia/Tokyo'));
    $filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . basename(__FILE__, ".php") . '_' . $date->format('YmdHis') . '.php';
    sleep(1);

    (new ResasApiClient())->find('api/v1/prefectures')
    ->set_kv_path("{n}[prefCode<=15].prefCode", "{n}[prefCode<=15].prefName")
    ->export_to($filename);

    // 配列として読み込めることを確認
    // prefCode=1の北海道からprefCode=15の新潟県までで絞り込まれていることを確認
    $data = include ($filename);
    logging($data, __FILE__);
} catch(Exception $e) {
    echo $e->getMessage();
}
