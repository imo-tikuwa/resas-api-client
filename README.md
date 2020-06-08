# resas-api-clientについて
地域経済分析システム（RESAS：リーサス）のデータを取得するAPIをラップしたクライアントプログラムです。  
php5.4以上であれば大体動くと思います。  
※使用するにはRESASのAPIキーが必要です。  

## インストール
```
composer require imo-tikuwa/resas-api-client
```

## 使い方

1. ResasApiClientのコンストラクタでAPIキーを渡してあげてください。
2. find関数で使用するAPIのアクションと必要に応じてパラメータをセット。
3. 以下のいずれかでデータを取得

|関数名|内容|
|---|---|
|toArray()|結果を配列として返します|
|toJson()|結果をjson文字列として返します|
|toObj()|結果をオブジェクトとして返します|
|toExport($filename)|結果を$filenameで指定したパスにインクルード可能なphpファイルとして出力します。|

## .envについて
以下の環境変数を作成しておくことでResasApiClientのコンストラクタ呼び出しの際にAPIキーを渡すことを省略できます。
```
ENV_RESAS_API_KEY="[API キー]"
```

## サンプルコード
以下は東京都の市区町村を取得するプログラムになります。
```
require_once (dirname(__DIR__) . "/vendor/autoload.php");

use TikuwaApp\Api\ResasApiClient;

$api_key = '[API キー]';
$resas_api_client = new ResasApiClient($api_key);
$result = $resas_api_client->find('api/v1/cities', ['prefCode' => '13'])->toArray();
```

---
以下は東京都の市区町村のうち「〇〇区」に一致する自治体のみを絞り込んでKey,Valueの配列で取得するプログラムになります。
```
require_once (dirname(__DIR__) . "/vendor/autoload.php");

use TikuwaApp\Api\ResasApiClient;

$resas_api_client = new ResasApiClient();
$result = $resas_api_client->find('api/v1/cities', ['prefCode' => '13'])
->setKeyValuePath("{n}[cityName=/^.+区$/].cityCode", "{n}[cityName=/^.+区$/].cityName")
->toArray();
```

---
以下は都道府県のコード値が15以下のものについてKey,Valueの配列で取得し、ファイルに出力するプログラムになります。
```
require_once (dirname(__DIR__) . "/vendor/autoload.php");

use TikuwaApp\Api\ResasApiClient;

$date = new DateTime();
$date->setTimezone(new DateTimeZone('Asia/Tokyo'));
$filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . basename(__FILE__, ".php") . '_' . $date->format('YmdHis') . '.php';

(new ResasApiClient())->find('api/v1/prefectures')
->setKeyValuePath("{n}[prefCode<=15].prefCode", "{n}[prefCode<=15].prefName")
->toExport($filename);
```

## リンク
[RESAS-API - 地域経済分析システム（RESAS）のAPI提供情報](https://opendata.resas-portal.go.jp/)  
[RESAS-API - API概要](https://opendata.resas-portal.go.jp/docs/api/v1/index.html)

## ライセンス
ソースの一部に[cakephp/utility](https://github.com/cakephp/utility)のプログラムを使用させていただいています。
