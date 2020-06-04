<?php
namespace TikuwaApp\Api;

use Exception;
use TikuwaApp\Utility\Hash;

/**
 * ResasApiClient
 *
 * @copyright Copyright (c) 2020 imo-tikuwa
 * @license https://opensource.org/licenses/mit-license.php MIT License
 */
class ResasApiClient {

	/**
	 * APIのエンドポイントURL
	 * @var string
	 */
	const API_ENDPOINT = "https://opendata.resas-portal.go.jp/";

	/**
	 * Constructor
	 * @param string $api_key RESAS-APIの利用登録時に発行されたAPIキー
	 *               未指定の場合、ENV_RESAS_API_KEYという名前の環境変数を参照します。
	 *               それでも見つからない場合はエラーを投げます。
	 */
	public function __construct($api_key = null)
	{
		if (is_null($api_key)) {
			$api_key = getenv('ENV_RESAS_API_KEY');
			if ($api_key === false) {
				throw new Exception("Please set the api key of RESAS-API");
			}
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
		return $this;
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
		return $this;
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
	 * @return array
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

		if ($response == '"400"') {
			throw new Exception("400 Bad Request");
		}
		$response = json_decode($response, true);
		if (isset($response['statusCode']) && $response['statusCode'] > 400) {
			throw new Exception(sprintf("Response error. code: %d, reason: %s", $response['statusCode'], @$response['message']));
		} else if (!isset($response['result'])) {
			throw new Exception("Response result not found.");
		}

		return $response['result'];
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
	 * 結果を配列で取得
	 * @return array
	 */
	public function to_array()
	{
		return $this->_call_api();
	}

	/**
	 * 結果をjson文字列で取得
	 * @return string
	 */
	public function to_json()
	{
		$response = $this->_call_api();
		return json_encode($response);
	}

	/**
	 * 結果をオブジェクトで取得
	 * @return object
	 */
	public function to_obj()
	{
		$response = $this->_call_api();
		return json_decode(json_encode($response));
	}

	/**
	 * 結果をKey Valueのペアに持ち替えた配列で取得する
	 * ※配列の持ち替えについてCakePHP3のUtility/Hashクラスを使用しています
	 * @param string $key_path key側として使用する値のパス構文
	 * @param string $value_path value側として使用する値のパス構文
	 */
	public function to_kv_array($key_path = null, $value_path = null)
	{
		if (is_null($key_path)) {
			throw new Exception('$key_path is required.');
		}
		if (is_null($value_path)) {
			throw new Exception('$value_path is required.');
		}
		$response = $this->_call_api();
		$response = Hash::combine($response, $key_path, $value_path);
		return $response;
	}

	/**
	 * 結果をファイルに再利用可能な形でエクスポート
	 * @param string $filename ファイル名
	 * @param string $under_php54 trueのとき配列の形式を[]からphp5.4未満のバージョンで読み込み可能なarray()に変更
	 * @return int|false
	 */
	public function export_to($filename = null, $under_php54 = false)
	{
		$response = $this->_call_api();
		$out  = "<?php" . PHP_EOL;
		$out .= "return ";
		$export = var_export($response, true);
		if (!$under_php54) {
			$export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
			$export = preg_replace("/    /", "\t", $export);
			$array = preg_split("/\r\n|\n|\r/", $export);
			$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
			$export = join(PHP_EOL, array_filter(["["] + $array));
		}
		$out .= $export;
		$out .= ";" . PHP_EOL;
		return file_put_contents($filename, $out);
	}
}