<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Dotenv\Dotenv;

// 1つ上のディレクトリに配置した.envを読み込み
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

/**
 * Monologによるロギング処理
 * @param array $result
 * @param string $filename 呼び出し元のphpファイル名
 */
function logging($result, $filename) {

	$date = new DateTime();
	$date->setTimezone(new DateTimeZone('Asia/Tokyo'));

	$filename = basename($filename, ".php") . "_{$date->format('YmdHis')}.log";
	$log = new Logger('api-request-log');
	$stream = new StreamHandler(dirname(__DIR__) . "/logs/{$filename}");
	$stream->setFormatter(new LineFormatter(null, null, true, true));
	$log->pushHandler($stream);
	$log->addInfo(print_r($result, true));
}