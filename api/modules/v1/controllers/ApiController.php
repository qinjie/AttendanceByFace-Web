<?php
namespace api\modules\v1\controllers;

use api\common\controllers\CustomActiveController;
use yii\rest\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UnauthorizedHttpException;
use yii\filters\VerbFilter;

class ApiController extends CustomActiveController {

    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'except' => ['home', 'error'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'except' => ['error'],
            'rules' => [
                [   
                    'allow' => true
                ],
            ],

            'denyCallback' => function ($rule, $action) {
                throw new UnauthorizedHttpException('You are not authorized');
            },
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'home' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    public function actionHome() {
        return [
            'msg' => 'Welcome to API attendance system'
        ];
    }

    public function actionAbc() {
        return 'abc';
    }

    public function actionError() {
        $error = Yii::$app->errorHandler->error;
        return $error->message;
    }
}
