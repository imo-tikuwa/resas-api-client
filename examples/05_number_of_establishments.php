<?php
require_once (dirname(__DIR__) . "/vendor/autoload.php");
require_once 'common.php';

use TikuwaApp\Api\ResasApiClient;

// 東京都千代田区の情報通信業の情報サービス業の事業者数を取得
try {
	$resas_api_client = new ResasApiClient();
	$result = $resas_api_client->find('api/v1/municipality/plant/perYear', [
			'prefCode' => '13',
			'cityCode' => '13101',
			'sicCode' => 'G',
			'simcCode' => '39',
	])->to_array();
	logging($result, __FILE__);
} catch(Exception $e) {
	echo $e->getMessage();
}
