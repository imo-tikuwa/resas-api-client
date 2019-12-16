<?php
require_once (dirname(__DIR__) . "/vendor/autoload.php");
require_once 'common.php';

use TikuwaApp\Api\ResasApiClient;

// 産業大分類を取得
$api_key = getenv('ENV_RESAS_API_KEY');
$resas_api_client = new ResasApiClient($api_key);
$result = $resas_api_client->get_data('api/v1/industries/broad');

// 結果をログに書き込み
logging($result, __FILE__);