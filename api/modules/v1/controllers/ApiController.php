<?php
namespace api\modules\v1\controllers;

use api\common\controllers\CustomActiveController;
use api\common\models\User;
use api\common\components\AccessRule;

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
            'except' => ['home', 'error', 'post'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'ruleConfig' => [
                'class' => AccessRule::className(),
            ],
            'rules' => [
                [   
                    'actions' => ['home', 'post'],
                    'allow' => true,
                    'roles' => ['?', '@'],
                ],
                [
                    'actions' => ['check-student'],
                    'allow' => true,
                    'roles' => [ User::ROLE_STUDENT ],
                ],
                [
                    'actions' => ['check-teacher'],
                    'allow' => true,
                    'roles' => [ User::ROLE_TEACHER ],
                ],
                [
                    'actions' => ['error'],
                    'allow' => false,
                ]
            ],

            'denyCallback' => function ($rule, $action) {
                throw new UnauthorizedHttpException('You are not authorized');
            },
        ];

        return $behaviors;
    }

    public function actionHome() {
        // return [
        //     'msg' => 'Welcome to API attendance system',
        // ];
        return [
            [
                "bookId" => "1",
                "name" => "Harry Potter and The Prisoner of Azkaban",
                "price" => "INR 700.00",
                "inStock" => "52"
            ],
           
            [
                "bookId" => "2",
                "name" => "Hamlet",
                "price" => "INR 1700.00",
                "inStock" => "47"
            ],

            [
                "bookId" => "3",
                "name" => "Willy Wonka and His Chocolate Factory",
                "price" => "INR 500.00",
                "inStock" => "48"
            ],
            
            [
                "bookId" => "4",
                "name" => "Before I Fall",
                "price" => "INR 750.00",
                "inStock" => "49"
            ]
            
        ];
    }

    public function actionPost() {
        return Yii::$app->request->bodyParams;        
        return [
            [
                "bookId" => "1",
                "name" => "Harry Potter and The Prisoner of Azkaban",
                "price" => "INR 700.00",
                "inStock" => "52"
            ],
           
            [
                "bookId" => "2",
                "name" => "Hamlet",
                "price" => "INR 1700.00",
                "inStock" => "47"
            ],

            [
                "bookId" => "3",
                "name" => "Willy Wonka and His Chocolate Factory",
                "price" => "INR 500.00",
                "inStock" => "48"
            ],
            
            [
                "bookId" => "4",
                "name" => "Before I Fall",
                "price" => "INR 750.00",
                "inStock" => "49"
            ]
            
        ];
    }

    public function actionCheckStudent() {
        return [
            'msg' => 'You are student',
        ];
    }

    public function actionCheckTeacher() {
        return [
            'msg' => 'You are teacher',
        ];
    }

    public function actionError() {
        $error = Yii::$app->errorHandler->error;
        return $error->message;
    }

    // public function afterAction($action, $result)
    // {
    //     $result = parent::afterAction($action, $result);
    //     // your custom code here
    //     return [
    //         'status' => '200',
    //         'data' => $result,
    //     ];
    // }
}
