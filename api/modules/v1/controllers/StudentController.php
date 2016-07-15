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
use api\common\models\Student;

use Yii;
use api\common\models\SignupStudentModel;
use api\common\models\LoginModel;
use api\common\models\ChangePasswordModel;
use api\common\models\PasswordResetModel;
use api\common\models\RegisterDeviceModel;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

class StudentController extends CustomActiveController
{
    public $uploadPath = '/upload/';
    public $modelClass = '';

    const CODE_INCORRECT_USERNAME = 0;
    const CODE_INCORRECT_PASSWORD = 1;
    const CODE_INCORRECT_DEVICE = 2;
    const CODE_UNVERIFIED_EMAIL = 3;
    const CODE_UNVERIFIED_DEVICE = 4;
    const CODE_UNVERIFIED_EMAIL_DEVICE = 5;
    const CODE_INVALID_ACCOUNT = 6;
    const CODE_DUPLICATE_DEVICE = 7;
    const CODE_INVALID_PASSWORD = 8;
    
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'ruleConfig' => [
                'class' => AccessRule::className(),
            ],
            'rules' => [
                [   
                    'actions' => ['profile'],
                    'allow' => true,
                    'roles' => [User::ROLE_STUDENT, User::ROLE_TEACHER],
                ],
            ],
            'denyCallback' => function ($rule, $action) {
                throw new UnauthorizedHttpException('You are not authorized');
            },
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'profile' => ['get'],
            ],
        ];

        return $behaviors;
    }

    public function actionProfile() {
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId])->toArray();
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');
        $student['email'] = 's'.substr($student['id'], 0, 8).'@connect.np.edu.sg';
        return $student;
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