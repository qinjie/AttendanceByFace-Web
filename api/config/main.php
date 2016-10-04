<?php
use api\modules\v1\Module as ModuleV1;
use yii\web\Response;
use yii\web\Request;
use yii\web\JsonResponseFormatter;
use common\models\User;
use yii\log\FileTarget;
use yii\web\UrlManager;

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'id' => 'api-v1',
            'basePath' => '@app/modules/v1',
            'class' => ModuleV1::className(),
            'controllerNamespace' => 'api\modules\v1\controllers',
        ],
    ],
    'components' => [
        'request' => [
            'class' => Request::className(),
            'enableCookieValidation' => false,
            'enableCsrfCookie' => false,
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'class' => Response::className(),
            'format' => Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'formatters' => [
                Response::FORMAT_JSON => [
                    'class' => JsonResponseFormatter::className(),
                    'prettyPrint' => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],
        'user' => [
            'identityClass' => User::className(),
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => FileTarget::className(),
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'class' => UrlManager::className(),
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
    ],
    'params' => $params,
];
