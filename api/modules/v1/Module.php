<?php
namespace api\modules\v1;

use yii\web\Response;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();
    }

    public function behaviors()
    {
        return [
            [
                'class' => 'yii\filters\ContentNegotiator',
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
                'languages' => [
                    'en',
                ],
            ],
        ];
    }

}
