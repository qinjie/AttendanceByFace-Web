<?php
/**
 * Created by PhpStorm.
 * User: qj
 * Date: 28/3/15
 * Time: 23:28
 */

namespace api\modules\v1\controllers;

use api\common\controllers\CustomActiveController;

use api\common\models\User;
use yii\filters\auth\HttpBasicAuth;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;

class UserController extends CustomActiveController
{
    public $modelClass = 'api\common\models\User';

    public function actionLogin() {
        return [
            'p1' => 1,
            'p2' => 2,
        ];
    }
}