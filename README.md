# resas-api-clientについて
地域経済分析システム（RESAS：リーサス）のデータを取得するAPIをラップしたクライアントプログラムです。  
php5.4以上であれば大体動くと思います。  
※使用するにはRESASのAPIキーが必要です。  

## インストール
```
composer require imo-tikuwa/resas-api-client
```

## .envについて
```
ENV_RESAS_API_KEY="[API キー]"
```

## 使い方

1. ResasApiClientのコンストラクタでAPIキーを渡してあげてください。
2. find関数もしくはset_action,set_parameters関数で使用するAPIのアクションとパラメータをセット。
3. 以下のいずれかでデータを取得

|関数名|内容|
|---|---|
|to_array()|結果を配列として返します|
|to_json()|結果をjson文字列として返します|
|to_obj()|結果をオブジェクトとして返します|
|export_to($filename, $under_php54)|結果を$filenameで指定したパスにインクルード可能なphpファイルとして出力します。<br />$under_php54はboolでデフォルトはfalse<br />$under_php54がtrueのときphp5.4未満のバージョンで読み取り可能な形式で出力します。|

## サンプルコード
以下は東京都の市区町村一覧を取得するプログラムになります。

```
require_once (dirname(__DIR__) . "/vendor/autoload.php");

use TikuwaApp\Api\ResasApiClient;

$api_key = getenv('ENV_RESAS_API_KEY');
$resas_api_client = new ResasApiClient($api_key);
$result = $resas_api_client->find('api/v1/cities', ['prefCode' => '13'])->to_array();
```
or
```
require_once (dirname(__DIR__) . "/vendor/autoload.php");

use TikuwaApp\Api\ResasApiClient;

$api_key = getenv('ENV_RESAS_API_KEY');
$resas_api_client = new ResasApiClient($api_key);
$result = $resas_api_client->set_action('api/v1/cities')->set_parameters(['prefCode' => '13'])->to_array();
```

## リンク
[RESAS-API - 地域経済分析システム（RESAS）のAPI提供情報](https://opendata.resas-portal.go.jp/)
[RESAS-API - API概要](https://opendata.resas-portal.go.jp/docs/api/v1/index.html)
