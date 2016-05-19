<?php
namespace api\modules\v1\controllers;

use api\common\controllers\CustomActiveController;
use yii\rest\Controller;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\AccessControl;
use yii\web\Response;

class ApiController extends CustomActiveController {

	public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $behaviors;
    }

    public function actionLogin() {
        return [
            'p1' => 1,
            'p2' => 2,
        ];
    }

    public function actionDashboard() {
        return [
            'p1' => 1,
            'p2' => 2,
        ];
    }
}