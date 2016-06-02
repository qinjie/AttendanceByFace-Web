<?php
namespace api\modules\v1\controllers;

use api\common\controllers\CustomActiveController;
use api\common\models\User;
use api\modules\v1\models\UploadForm;
use api\common\components\AccessRule;

use yii\rest\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

class ApiController extends CustomActiveController {

    public $documentPath = '/upload/';

    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'except' => ['home', 'error', 'post', 'upload'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'ruleConfig' => [
                'class' => AccessRule::className(),
            ],
            'rules' => [
                [   
                    'actions' => ['home', 'post', 'upload'],
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
    }

    public function actionCheckStudent() {
        return 'You are student';
    }

    public function actionCheckTeacher() {
        return 'You are teacher';
    }

    public function actionUpload()
    {
        $allowedTypes = ['image/png', 'image/jpg'];
        $fileType = $_FILES['profileImg']['type'];
        if (!in_array($fileType, $allowedTypes))
            throw new BadRequestHttpException('Profile image must be png or jpg');

        $postdata = fopen( $_FILES[ 'profileImg' ][ 'tmp_name' ], "r" );
        /* Get file extension */
        $extension = substr( $_FILES[ 'profileImg' ][ 'name' ], strrpos( $_FILES[ 'profileImg' ][ 'name' ], '.' ) );

        /* Generate unique name */
        $filename = $_SERVER['DOCUMENT_ROOT'] . $this->documentPath . uniqid() . $extension;
        // return $filename;

        /* Open a file for writing */
        $fp = fopen( $filename, "w" );

        /* Read the data 1 KB at a time
          and write to the file */
        while( $data = fread( $postdata, 1024 ) )
            fwrite( $fp, $data );

        /* Close the streams */
        fclose( $fp );
        fclose( $postdata );

        /* the result object that is sent to client*/
        return array_merge([
            'filename' => $filename,
            'document' => $_FILES[ 'profileImg' ][ 'name' ],
            'created_at' => date( "Y-m-d H:i:s" ),
        ], Yii::$app->request->bodyParams);
    }

    public function actionError() {
        $error = Yii::$app->errorHandler->error;
        return $error->message;
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        // your custom code here
        return [
            'status' => '200',
            'data' => $result,
        ];
    }
}
