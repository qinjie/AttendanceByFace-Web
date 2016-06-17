<?php
/**
 * Created by PhpStorm.
 * User: qj
 * Date: 28/3/15
 * Time: 23:28
 */

namespace api\modules\v1\controllers;

use api\common\controllers\CustomActiveController;
use api\common\helpers\TokenHelper;
use api\common\models\UserToken;
use api\common\models\User;
use api\common\components\AccessRule;

use Yii;
use api\common\models\SignupModel;
use api\common\models\LoginModel;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

class UserController extends CustomActiveController
{
    public $uploadPath = '/upload/';
    public $modelClass = '';
    
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'except' => ['login', 'signup', 'confirm-email'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'ruleConfig' => [
                'class' => AccessRule::className(),
            ],
            'rules' => [
                [   
                    'actions' => ['login', 'signup', 'confirm-email'],
                    'allow' => true,
                    'roles' => ['?'],
                ],
                [
                    'actions' => ['logout', 'person-id', 'face-id', 'set-person-id', 'set-face-id'],
                    'allow' => true,
                    'roles' => ['@'],
                ]
            ],
            'denyCallback' => function ($rule, $action) {
                throw new UnauthorizedHttpException('You are not authorized');
            },
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'login' => ['post'],
                'signup' => ['post'],
                'logout' => ['get'],
                'register-device' => ['post'],
            ],
        ];

        return $behaviors;
    }

    public function actionLogin() {
    	$request = Yii::$app->request;
    	$bodyParams = $request->bodyParams;
        $username = $bodyParams['username'];
        $password = $bodyParams['password'];
        $device_hash = $bodyParams['device_hash'];

    	$model = new LoginModel();
    	$model->username = $username;
    	$model->password = $password;
        $model->device_hash = $device_hash;
    	if ($user = $model->login()) {
            UserToken::deleteAll(['user_id' => $user->id]);
    		$token = TokenHelper::createUserToken($user->id);
			return [
                'token' => $token->token,
            ];
    	}
        // throw new BadRequestHttpException('Invalid username or password');
        // throw new BadRequestHttpException($user->errors);
        return $user->errors;
    }

    public function actionSignup() {
        // $allowedTypes = ['image/png', 'image/jpg'];
        // $fileType = $_FILES['profileImg']['type'];
        // if (!in_array($fileType, $allowedTypes))
        //     throw new BadRequestHttpException('Profile image must be png or jpg');

        // $postdata = fopen( $_FILES[ 'profileImg' ][ 'tmp_name' ], "r" );
        // /* Get file extension */
        // $extension = substr( $_FILES[ 'profileImg' ][ 'name' ], strrpos( $_FILES[ 'profileImg' ][ 'name' ], '.' ) );

        //  Generate unique name 
        // $fileUrl = $this->documentPath . uniqid() . $extension;
        // $filename = $_SERVER['DOCUMENT_ROOT'] . $fileUrl;

        // /* Open a file for writing */
        // $fp = fopen( $filename, "w" );

        // /* Read the data 1 KB at a time
        //   and write to the file */
        // while( $data = fread( $postdata, 1024 ) )
        //     fwrite( $fp, $data );

        // /* Close the streams */
        // fclose( $fp );
        // fclose( $postdata );

    	$bodyParams = Yii::$app->request->bodyParams;

    	$model = new SignupModel();
    	$model->username = $bodyParams['username'];
    	$model->email = $bodyParams['email'];
    	$model->password = $bodyParams['password'];
        $model->role = isset($bodyParams['role']) ? $bodyParams['role'] : User::ROLE_STUDENT;
        $model->device_hash = $bodyParams['device_hash'];
		if ($user = $model->signup()) {
			$token = TokenHelper::createUserToken($user->id);
			return [
                'token' => $token->token,
            ];
		}
        throw new BadRequestHttpException('Invalid data');
    }

    public function actionLogout() {
    	$id = Yii::$app->user->identity->id;
    	UserToken::deleteAll(['user_id' => $id, 'action' => TokenHelper::TOKEN_ACTION_ACCESS]);
		return 'logout successful';
    }

    public function actionConfirmEmail($token = null) {
        if (empty($token) || !is_string($token)) {
            $viewPath = '/attendance-system/api/views/confirmation-error.html';
            header('Location: '.$viewPath);
            exit(0);
        }
        $userId = TokenHelper::authenticateToken($token, true, TokenHelper::TOKEN_ACTION_ACTIVATE_ACCOUNT);
        $user = User::findOne([
            'id' => $userId, 
            'status' => [User::STATUS_WAIT_EMAIL_DEVICE, User::STATUS_WAIT_EMAIL],
        ]);
        if (!$userId) {
            $viewPath = '/attendance-system/api/views/confirmation-error.html';
            header('Location: '.$viewPath);
            exit(0);
        }

        if ($user->status == User::STATUS_WAIT_EMAIL_DEVICE)
            $user->status = User::STATUS_WAIT_DEVICE;
        else if ($user->status == User::STATUS_WAIT_EMAIL)
            $user->status = User::STATUS_ACTIVE;
        
        UserToken::removeEmailConfirmToken($user->id, $token);
        if ($user->save()) {
            $viewPath = '/attendance-system/api/views/confirmation-success.html';
            header('Location: '.$viewPath);
            exit(0);
        }
        $viewPath = '/attendance-system/api/views/confirmation-error.html';
        header('Location: '.$viewPath);
        exit(0);
    }

    public function actionRegisterDevice() {
        return 'register device';
    }

    public function actionPersonId() {
        $userId = Yii::$app->user->identity->id;
        $query = Yii::$app->db->createCommand('
            select id as user_id,
                   person_id 
             from user 
             where id = :user_id
        ')
        ->bindValue(':user_id', $userId);
        return $query->queryOne();
    }

    public function actionFaceId() {
        $userId = Yii::$app->user->identity->id;
        $query = Yii::$app->db->createCommand('
            select id as user_id,
                   face_id 
             from user 
             where id = :user_id
        ')
        ->bindValue(':user_id', $userId);
        $result = $query->queryOne();
        if ($result['face_id'])
            $result['face_id'] = json_decode($result['face_id']);
        return $result;
    }

    public function actionSetPersonId() {
        $userId = Yii::$app->user->identity->id;
        $request = Yii::$app->request;
        $bodyParams = $request->bodyParams;
        $person_id = $bodyParams;
        $query = Yii::$app->db->createCommand('
            update user 
             set person_id = :person_id 
             where id = :user_id
        ')
        ->bindValue(':person_id', $person_id)
        ->bindValue(':user_id', $userId);
        return [
            'result' => $query->execute(),
        ];
    }

    public function actionSetFaceId() {
        $userId = Yii::$app->user->identity->id;
        $request = Yii::$app->request;
        $bodyParams = $request->bodyParams;
        $face_id = json_encode($bodyParams);
        $query = Yii::$app->db->createCommand('
            update user 
             set face_id = :face_id 
             where id = :user_id
        ')
        ->bindValue(':face_id', $face_id)
        ->bindValue(':user_id', $userId);
        return [
            'result' => $query->execute(),
        ];
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