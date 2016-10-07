<?php
namespace api\modules\v1;

use yii\web\Response;
use yii\base\Module as BaseModule;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;

class Module extends BaseModule
{
    public function init()
    {
        parent::init();
    }

    public function behaviors()
    {
        return [
            [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
                'languages' => [
                    'en',
                ],
            ],
            'corsFilter' => [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'PUT', 'POST', 'DELETE', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['Content-Type', 'Authorization'],
                    'Access-Control-Allow-Credentials' => true,
                ]
            ],
        ];
    }

}
