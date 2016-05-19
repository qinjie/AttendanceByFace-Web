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

use Yii;
use api\common\models\SignupModel;
use api\common\models\LoginModel;
use yii\filters\auth\HttpBasicAuth;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;

class UserController extends CustomActiveController
{
    public $modelClass = '';

    public function actionLogin() {
    	$request = Yii::$app->request;
    	$bodyParams = $request->bodyParams;

    	$model = new LoginModel();
    	$model->username = $bodyParams['username'];
    	$model->password = $bodyParams['password'];
    	if ($user = $model->login()) {
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
		if ($user = $model->signup()) {
			$token = TokenHelper::createUserToken($user->id);
			return $token->token;
		}
    	return $model->errors;
    }

    public function actionLogout() {
    	$id = Yii::$app->user->identity->id;
    	UserToken::deleteAll(['user_id' => $id]);
		return 'logout successful';
    }
}