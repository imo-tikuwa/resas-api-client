<?php
namespace TikuwaApp\Api;

use Exception;

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
	public function __construct($api_key = null)
	{
		if (is_null($api_key)) {
			throw new Exception("Please set the api key of RESAS-API");
		}
		$this->api_key = $api_key;
	}

	/**
	 * RESAS-APIの利用登録時に発行されたAPIキー
	 * @var string
	 */
	private $api_key;

	/**
	 * リクエストする際のアクション
	 * @var string
	 */
	private $action;

	/**
	 * リクエストする際の検索条件
	 * @var string
	 */
	private $parameters;

	/**
	 * リクエストする際のアクション をセット
	 */
	public function set_action($action)
	{
		$this->action = $action;
	}

	/**
	 * リクエストする際のアクション を取得
	 */
	public function get_action()
	{
		return $this->action;
	}

	/**
	 * リクエストする際の検索条件 をセット
	 */
	public function set_parameters($parameters)
	{
		$this->parameters = $parameters;
	}

	/**
	 * リクエストする際の検索条件 を取得
	 */
	public function get_parameters()
	{
		return $this->parameters;
	}

	/**
	 * RESASにcurlでAPIリクエストする
	 * @param string $action エンドポイント以下のURI
	 * @param array $parameters パラメータ配列
	 * @return json文字列のレスポンス
	 */
	private function _call_api()
	{
		$action = $this->get_action();
		if (is_null($action)) {
			throw new Exception("Please set the request action of RESAS-API");
		}
		$parameters = $this->get_parameters();

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
		curl_close($curl);

		return $response;
	}

	/**
	 * APIリクエストを行いデータを取得する
	 * @param string $action エンドポイント以下のURI
	 * @param array $parameters パラメータ配列
	 * @return $this
	 */
	public function find($action, $parameters = null)
	{
		$this->set_action($action);
		$this->set_parameters($parameters);
		return $this;
	}

	/**
	 * 結果をjson文字列で取得
	 * @return string
	 */
	public function to_json()
	{
		return $this->_call_api();
	}

	/**
	 * 結果を配列で取得
	 * @return array
	 */
	public function to_array()
	{
		$response = $this->_call_api();
		return json_decode($response, true);
	}

	/**
	 * 結果をオブジェクトで取得
	 * @return object
	 */
	public function to_obj()
	{
		$response = $this->_call_api();
		return json_decode($response);
	}

	/**
	 * 結果をファイルに再利用可能な形でエクスポート
	 * @param string $filename ファイル名
	 * @param string $under_php54 trueのとき配列の形式を[]からphp5.4未満のバージョンで読み込み可能なarray()に変更
	 * @return string APIリクエストのレスポンスに含まれるメッセージ
	 */
	public function export_to($filename = null, $under_php54 = false)
	{
		$response = $this->_call_api();
		$response = json_decode($response, true);
		$out  = "<?php" . PHP_EOL;
		$out .= "return ";
		$export = var_export($response['result'], true);
		if (!$under_php54) {
			$export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
			$export = preg_replace("/    /", "\t", $export);
			$array = preg_split("/\r\n|\n|\r/", $export);
			$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
			$export = join(PHP_EOL, array_filter(["["] + $array));
		}
		$out .= $export;
		$out .= ";" . PHP_EOL;
		file_put_contents($filename, $out);
		return $response['message'];
	}
}