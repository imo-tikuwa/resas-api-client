<?php
require_once (dirname(__DIR__) . "/vendor/autoload.php");
require_once 'common.php';

use TikuwaApp\Api\ResasApiClient;

// 都道府県の一覧を取得する
try {
    $date = new DateTime();
    $date->setTimezone(new DateTimeZone('Asia/Tokyo'));
    $filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . basename(__FILE__, ".php") . '_' . $date->format('YmdHis') . '.php';
    sleep(1);

    (new ResasApiClient())->find('api/v1/prefectures')->export_to($filename);

    // 配列として読み込めることを確認
    $data = include ($filename);
    logging($data, __FILE__);
} catch(Exception $e) {
    echo $e->getMessage();
}
