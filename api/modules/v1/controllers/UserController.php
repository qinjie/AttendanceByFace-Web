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

class UserController extends CustomActiveController
{
    public $modelClass = '';
    
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['logout'],
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
                    'actions' => ['logout'],
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
            ],
        ];

        return $behaviors;
    }

    public function actionLogin() {
    	$request = Yii::$app->request;
    	$bodyParams = $request->bodyParams;
        // $username = $request->get('username');
        // $password = $request->get('password');
        $username = $bodyParams['username'];
        $password = $bodyParams['password'];

    	$model = new LoginModel();
    	$model->username = $username;
    	$model->password = $password;
    	if ($user = $model->login()) {
            UserToken::deleteAll(['user_id' => $user->id]);
    		$token = TokenHelper::createUserToken($user->id);
			return $token->token;
    	}
    	return $model->errors;
    }

    public function actionSignup() {
    	$request = Yii::$app->request;
    	$bodyParams = $request->bodyParams;

    	$model = new SignupModel();
    	$model->username = $bodyParams['username'];
    	$model->email = $bodyParams['email'];
    	$model->password = $bodyParams['password'];
        $model->role = isset($bodyParams['role']) ? $bodyParams['role'] : User::ROLE_USER;
		if ($user = $model->signup()) {
			$token = TokenHelper::createUserToken($user->id);
			return $token->token;
		}
    	return $model->errors;
    }

    public function actionLogout() {
    	$id = Yii::$app->user->identity->id;
    	UserToken::deleteAll(['user_id' => $id, 'action' => TokenHelper::TOKEN_ACTION_ACCESS]);
		return 'logout successful';
    }

    public function actionConfirmEmail($token = null) {
        if (empty($token) || !is_string($token)) {
            throw new BadRequestHttpException('Email confirm token cannot be blank.');
        }
        $userId = TokenHelper::authenticateToken($token, true, TokenHelper::TOKEN_ACTION_ACTIVATE_ACCOUNT);
        $user = User::findOne(['id' => $userId, 'status' => User::STATUS_WAIT]);
        if (!$userId) {
            throw new BadRequestHttpException('Wrong Email confirm token.');
        }
        $user->status = User::STATUS_ACTIVE;
        UserToken::removeEmailConfirmToken($user->id, $token);
        if ($user->save()) {
            return 'Confirm email successfully';
        }
        throw new BadRequestHttpException('Error! Failed to confirm your email.');
    }
}