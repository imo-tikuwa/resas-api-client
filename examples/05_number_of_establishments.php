<?php
require_once (dirname(__DIR__) . "/vendor/autoload.php");
require_once 'common.php';

use TikuwaApp\Api\ResasApiClient;

// 東京都千代田区の情報通信業の情報サービス業の事業者数を取得
$api_key = getenv('ENV_RESAS_API_KEY');
$resas_api_client = new ResasApiClient($api_key);
$result = $resas_api_client->get_data('api/v1/municipality/plant/perYear', [
		'prefCode' => '13',
		'cityCode' => '13101',
		'sicCode' => 'G',
		'simcCode' => '39',
]);

// 結果をログに書き込み
logging($result, __FILE__);