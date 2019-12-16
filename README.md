# resas-api-clientについて
地域経済分析システム（RESAS：リーサス）のデータを取得するAPIをラップしたクライアントプログラムです。  
php5.4以上であれば大体動くと思います。  
※使用するにはRESASのAPIキーが必要です。  

## 使い方
1. git cloneする
2. composer installする
3. .envを作成する

## .envについて
```
ENV_RESAS_API_KEY="[API キー]"
```

## サンプルプログラム
1. ResasApiClientのコンストラクタでAPIキーを渡してあげてください。
2. get_data関数で使用するAPIのアクションとパラメータを渡してあげてください。

以下は東京都の市区町村一覧を取得するプログラムになります。

```
require_once (dirname(__DIR__) . "/vendor/autoload.php");

use TikuwaApp\Api\ResasApiClient;

$api_key = getenv('ENV_RESAS_API_KEY');
$resas_api_client = new ResasApiClient($api_key);
$result = $resas_api_client->get_data('api/v1/cities', ['prefCode' => '13']);
```
