<?php
namespace api\components;

use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;

class CustomActiveController extends ActiveController
{
    public $modelClass = '';

    public function behaviors()
    {
        $behaviors = ArrayHelper::merge([
            [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'PUT', 'POST', 'DELETE', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['Content-Type', 'Authorization'],
                    'Access-Control-Allow-Credentials' => true,
                ]
            ],
        ], parent::behaviors());

        return $behaviors;
    }
}
