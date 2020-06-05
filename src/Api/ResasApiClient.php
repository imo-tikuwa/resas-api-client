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
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * リクエストする際のアクション を取得
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * リクエストする際の検索条件 をセット
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * リクエストする際の検索条件 を取得
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Hashクラスで絞り込む際のkey側のパス構文 をセット
     */
    private function setKeyPath($key_path)
    {
        $this->key_path = $key_path;
        return $this;
    }

    /**
     * Hashクラスで絞り込む際のkey側のパス構文 を取得
     */
    public function getKeyPath()
    {
        return $this->key_path;
    }

    /**
     * Hashクラスで絞り込む際のvalue側のパス構文 をセット
     */
    private function setValuePath($value_path)
    {
        $this->value_path = $value_path;
        return $this;
    }

    /**
     * Hashクラスで絞り込む際のvalue側のパス構文 を取得
     */
    public function getValuePath()
    {
        return $this->value_path;
    }

    /**
     * RESASにcurlでAPIリクエストする
     * @return array
     */
    private function callApi($return_to = 'array')
    {
        $action = $this->getAction();
        if (is_null($action)) {
            throw new Exception("Please set the request action of RESAS-API");
        }
        $parameters = $this->getParameters();

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

        // レスポンスチェック
        if ($response == '"400"') {
            throw new Exception("400 Bad Request");
        }
        $response = json_decode($response, true);
        if (isset($response['statusCode']) && $response['statusCode'] > 400) {
            throw new Exception(sprintf("Response error. code: %d, reason: %s", $response['statusCode'], @$response['message']));
        } else if (!isset($response['result'])) {
            throw new Exception("Response result not found.");
        }

        $result = $response['result'];

        // CakePHP3のUtility/Hash::combine()による結果の絞込
        $key_path = $this->getKeyPath();
        $value_path = $this->getValuePath();
        if (!is_null($key_path) && !is_null($value_path)) {
            $result = Hash::combine($result, $key_path, $value_path);
        }

        // レスポンスタイプを元にレスポンスを整形
        if ($return_to === 'json') {
            return json_encode($result);
        } else if ($return_to === 'object') {
            return json_decode(json_encode($result));
        }
        return $result;
    }

    /**
     * APIリクエストを行いデータを取得する
     * @param string $action エンドポイント以下のURI
     * @param array $parameters パラメータ配列
     * @return $this
     */
    public function find($action, $parameters = null)
    {
        $this->setAction($action);
        $this->setParameters($parameters);
        return $this;
    }

    /**
     * 各種セット済みの条件をクリアする
     * @return $this
     */
    public function clear()
    {
        $this->setAction(null);
        $this->setParameters(null);
        $this->setKeyPath(null);
        $this->setValuePath(null);
        return $this;
    }

    /**
     * 結果をKey Valueのペアに持ち替えた配列で取得する
     * ※配列の持ち替えについてCakePHP3のUtility/Hashクラスを使用しています
     * @param string $key_path key側として使用する値のパス構文
     * @param string $value_path value側として使用する値のパス構文
     * @return $this
     */
    public function setKeyValuePath($key_path = null, $value_path = null)
    {
        if (is_null($key_path)) {
            throw new Exception('$key_path is required.');
        }
        if (is_null($value_path)) {
            throw new Exception('$value_path is required.');
        }
        $this->setKeyPath($key_path);
        $this->setValuePath($value_path);
        return $this;
    }

    /**
     * 結果を配列で取得
     * @return array
     */
    public function toArray()
    {
        return $this->callApi('array');
    }

    /**
     * 結果をjson文字列で取得
     * @return string
     */
    public function toJson()
    {
        return $this->callApi('json');
    }

    /**
     * 結果をオブジェクトで取得
     * @return array
     */
    public function toObject()
    {
        return $this->callApi('object');
    }

    /**
     * 結果をファイルに再利用可能な形でエクスポート
     * @param string $filename ファイル名
     * @return int|false
     */
    public function toExport($filename = null)
    {
        $result = $this->callApi('array');
        $out  = "<?php" . PHP_EOL;
        $out .= "return ";
        $export = var_export($result, true);
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