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
	 * Destructor
	 */
	public function __destruct()
    {
        if ($this->last) {
            return $this->_call_api();
        }
    }

	/**
	 * メソッドチェインの最後か判定
	 * @link https://qiita.com/ngyuki/items/93075c5472c3417ff355
	 */
	private $last = false;

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
	 * レスポンスのタイプ
	 * @var string
	 */
	private $response_type;

	/**
	 * Hashクラスで絞り込む際のkey側のパス構文
	 * @var string
	 */
	private $key_path = null;

	/**
	 * Hashクラスで絞り込む際のvalue側のパス構文
	 * @var string
	 */
	private $value_path = null;

	/**
	 * リクエストする際のアクション をセット
	 */
	private function set_response_type($response_type)
	{
		$this->response_type = $response_type;
		return $this;
	}

	/**
	 * Hash::combine()でkey側として使用する値のパス構文 をセット
	 */
	private function set_key_path($key_path)
	{
		$this->key_path = $key_path;
		return $this;
	}

	/**
	 * Hash::combine()でvalue側として使用する値のパス構文 をセット
	 */
	private function set_value_path($value_path)
	{
		$this->value_path = $value_path;
		return $this;
	}

	/**
	 * リクエストする際のアクション をセット
	 */
	public function set_action($action)
	{
		$this->action = $action;
		return $this;
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
	 * RESASにcurlでAPIリクエストする
	 * @return array
	 */
	private function _call_api()
	{
		if (is_null($this->response_type)) {
			throw new Exception("Please call the api response_type methond.");
		} else if (is_null($this->action)) {
			throw new Exception("Please set the request action of RESAS-API");
		}

		$url = self::API_ENDPOINT . $this->action;
		if (!is_null($this->parameters)) {
			$url .=  "?" . http_build_query($this->parameters);
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

		// レスポンスのチェック
		if ($response == '"400"') {
			throw new Exception("400 Bad Request");
		}
		$response = json_decode($response, true);
		if (isset($response['statusCode']) && $response['statusCode'] > 400) {
			throw new Exception(sprintf("Response error. code: %d, reason: %s", $response['statusCode'], @$response['message']));
		} else if (!isset($response['result'])) {
			throw new Exception("Response result not found.");
		}

		// レスポンスタイプを元にレスポンスを整形
		if ($this->response_type === 'json') {
			return json_encode($response['result']);
		} else if ($this->response_type === 'object') {
			return json_decode(json_encode($response['result']));
		} else if ($this->response_type === 'kv_array') {
			return Hash::combine($response['result'], $this->key_path, $this->value_path);
		}
		return $response['result'];
	}

	/**
	 * メソッドチェインの最後判定フラグをtrueに代えた$thisのクローンを返す
	 * @link https://qiita.com/ngyuki/items/93075c5472c3417ff355
	 * @return $this
	 */
	private function ret()
	{
		$this->last = false;
        $obj = clone $this;
        $obj->last = true;
        return $obj;
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
	 * @return $this
	 */
	public function set_response_type_to_array()
	{
		$this->set_response_type('array');
		$this->set_key_path(null);
		$this->set_value_path(null);
		return $this;
	}

	/**
	 * 結果をjson文字列で取得
	 * @return $this
	 */
	public function set_response_type_to_json()
	{
		$this->set_response_type('json');
		$this->set_key_path(null);
		$this->set_value_path(null);
		return $this->ret();
	}

	/**
	 * 結果をオブジェクトで取得
	 * @return $this
	 */
	public function set_response_type_to_object()
	{
		$this->set_response_type('object');
		$this->set_key_path(null);
		$this->set_value_path(null);
		return $this->ret();
	}

	/**
	 * 結果をKey Valueのペアに持ち替えた配列で取得する
	 * ※配列の持ち替えについてCakePHP3のUtility/Hashクラスを使用しています
	 * @param string $key_path Hash::combine()でkey側として使用する値のパス構文(必須)
	 * @param string $value_path Hash::combine()でvalue側として使用する値のパス構文(必須)
	 * @return $this
	 */
	public function set_response_type_to_kv_array($key_path = null, $value_path = null)
	{
		if (is_null($key_path)) {
			throw new Exception('$key_path is required.');
		}
		if (is_null($value_path)) {
			throw new Exception('$value_path is required.');
		}
		$this->response_type = 'kv_array';
		$this->set_key_path($key_path);
		$this->set_value_path($value_path);
		return $this->ret();
	}

	/**
	 * 明示的なAPIリクエスト実行
	 * @return string|array
	 */
	public function call() {
		$this->last = false;
		return $this->_call_api();
	}

	/**
	 * 結果をファイルに再利用可能な形でエクスポート
	 * @param string $filename ファイル名
	 * @return int|false
	 */
	public function export_to($filename = null)
	{
		$response = $this->_call_api();
		$out  = "<?php" . PHP_EOL;
		$out .= "return ";
		$export = var_export($response, true);
		$export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
		$export = preg_replace("/    /", "\t", $export);
		$array = preg_split("/\r\n|\n|\r/", $export);
		$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
		$export = join(PHP_EOL, array_filter(["["] + $array));
		$out .= $export;
		$out .= ";" . PHP_EOL;
		return file_put_contents($filename, $out);
	}
}