<?php
namespace TikuwaApp\Api;

class ResasApiClient {

	/**
	 * APIのエンドポイントURL
	 * @var string
	 */
	const API_ENDPOINT = "https://opendata.resas-portal.go.jp/";

	/**
	 * Constructor
	 * @param string $api_key RESAS-APIの利用登録時に発行されたAPIキー
	 */
	public function __construct($api_key) {
		$this->api_key = $api_key;
	}

	/**
	 * RESAS-APIの利用登録時に発行されたAPIキー
	 * @var string
	 */
	private $api_key;

	/**
	 * APIリクエストを行いデータを取得する
	 * @param string $action エンドポイント以下のURI
	 * @param array $parameters パラメータ配列
	 */
	public function get_data($action, $parameters = null) {

		$url = self::API_ENDPOINT . $action;
		if (!is_null($parameters)) {
			$url .=  "?" . http_build_query($parameters);
		}

		$curl = curl_init();
		curl_setopt_array($curl, [
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_TIMEOUT => 10,
				CURLOPT_HTTPHEADER => [
						"X-API-KEY: {$this->api_key}",
				],
		]);

		$response = curl_exec($curl);
		$result = json_decode($response, true);
		curl_close($curl);

		return $result;
	}
}