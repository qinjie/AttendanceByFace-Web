<?php
use api\modules\v1\Module as ModuleV1;
use yii\web\Response;
use yii\web\Request;
use yii\web\JsonResponseFormatter;
use common\models\User;
use yii\log\FileTarget;
use yii\web\UrlManager;
use yii\rest\UrlRule as RestUrlRule;

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
            'enableStrictParsing' => true,
            'rules' => [
                # User API
                'POST <version:\w+>/lecturer/login' => '<version>/user/login-lecturer',
                'POST <version:\w+>/student/login' => '<version>/user/login-student',
                'POST <version:\w+>/student/signup' => '<version>/user/signup-student',
                'POST <version:\w+>/lecturer/signup' => '<version>/user/signup-lecturer',
                'GET <version:\w+>/user/confirm-email' => '<version>/user/confirm-email',
                'POST <version:\w+>/student/register-device' => '<version>/user/register-device',
                [
                    'class' => RestUrlRule::className(),
                    'pluralize' => false,
                    'controller' => 'v1/user',
                    'extraPatterns' => [
                        'GET,POST mine' => 'mine',
                        'POST logout' => 'logout',
                        'POST change-password' => 'change-password',
                        'POST reset-password' => 'reset-password',
                        'POST allow-train-face' => 'allow-train-face',
                        'POST disallow-train-face' => 'disallow-train-face'
                    ]
                ],
                [
                    'class' => RestUrlRule::className(),
                    'pluralize' => false,
                    'controller' => ['v1/student', 'v1/lecturer'],
                    'extraPatterns' => [
                        'GET profile' => 'profile'
                    ]
                ],

                # Timetable API
                [
                    'class' => RestUrlRule::className(),
                    'pluralize' => false,
                    'controller' => 'v1/attendance',
                    'extraPatterns' => [
                        'GET day' => 'day',
                        'GET week' => 'week',
                        'GET history' => 'history',
                        'GET semester' => 'semester',
                        'POST face' => 'face'
                    ]
                ]
            ],
        ],
    ],
    'params' => $params,
];
