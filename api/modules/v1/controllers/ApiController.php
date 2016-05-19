<?php
namespace api\modules\v1\controllers;

use api\common\controllers\CustomActiveController;
use yii\rest\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UnauthorizedHttpException;

class ApiController extends CustomActiveController {

    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'except' => ['home'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [   
                    'allow' => true
                ],
            ],

            'denyCallback' => function ($rule, $action) {
                throw new UnauthorizedHttpException('You are not authorized');
            },
        ];

        return $behaviors;
    }
    public function actionHome() {
        return 'Welcome to API attendance system';
    }
}