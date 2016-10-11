<?php

namespace api\modules\v1\controllers;

use common\models\User;
use common\models\Lecturer;
use common\models\Student;
use common\models\UserToken;
use common\models\search\UserSearch;
use common\components\TokenHelper;
use api\components\CustomActiveController;
use api\components\AccessRule;
use api\models\LoginForm;
use api\models\SignupForm;
use api\models\RegisterDeviceForm;
use api\models\ChangePasswordForm;
use api\models\ResetPasswordForm;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;
use yii\filters\VerbFilter;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends CustomActiveController
{
    public $modelClass = 'common\models\User';

    # Include envelope
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    const CODE_INCORRECT_USERNAME = 10;
    const CODE_INCORRECT_PASSWORD = 11;
    const CODE_INCORRECT_DEVICE = 12;
    const CODE_UNVERIFIED_EMAIL = 13;
    const CODE_UNVERIFIED_DEVICE = 14;
    const CODE_UNVERIFIED_EMAIL_DEVICE = 15;
    const CODE_INVALID_ACCOUNT = 16;
    const CODE_DUPLICATE_DEVICE = 17;
    const CODE_INVALID_PASSWORD = 18;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => HttpBearerAuth::className(),
                'except' => ['login-lecturer', 'login-student', 'signup-student', 'signup-lecturer',
                    'reset-password', 'register-device', 'confirm-email'],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['login-lecturer', 'login-student', 'signup-student', 'signup-lecturer',
                            'reset-password', 'register-device', 'confirm-email'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'change-password', 'mine'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['allow-train-face', 'disallow-train-face'],
                        'allow' => true,
                        'roles' => [User::ROLE_LECTURER],
                    ],
                    [
                        'actions' => ['check-train-face'],
                        'allow' => true,
                        'roles' => [User::ROLE_STUDENT],
                    ]
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new UnauthorizedHttpException('You are not authorized');
                },
            ]
        ];
    }

    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'],
            $actions['update'], $actions['delete']);
        return $actions;
    }

    public function actionMine() {
        if (Yii::$app->request->isGet) {
            $params = Yii::$app->request->queryParams;
            $fields = [];
            if (isset($params['fields']))
                $fields = explode(',', $params['fields']);
            $result = Yii::$app->user->identity->toArray($fields);
            return $result;
        } else if (Yii::$app->request->isPost) {
            $postBody = Yii::$app->request->post();

            // Prevent updating these attributes
            unset($postBody['username'], $postBody['auth_key'],
                $postBody['password_hash'], $postBody['status'],
                $postBody['role'], $postBody['created_at'], $postBody['updated_at'],
                $postBody['device_hash'], $postBody['name']);

            if (isset($postBody['face_id']))
                $postBody['face_id'] = json_encode($postBody['face_id']);
            $user = Yii::$app->user->identity;
            if ($user->load($postBody, '') && $user->save()) {
                return $user->toArray([
                    'person_id', 'face_id', 'username', 'email'
                ]);
            } else return null;
        }
    }

    public function actionLoginLecturer()
    {
        $model = new LoginForm([
            'scenario' => LoginForm::SCENARIO_LECTURER
        ]);
        if ($model->load(Yii::$app->request->post(), '') && $user = $model->login()) {
            $lecturer = Lecturer::findOne(['user_id' => $user->id])->toArray([
                'name', 'acad', 'email'
            ]);
            UserToken::deleteAll(['user_id' => $user->id, 'action' => TokenHelper::TOKEN_ACTION_ACCESS]);
            $userToken = TokenHelper::createUserToken($user->id);
            $lecturer['token'] = $userToken->token;
            return $lecturer;
        } else {
            if ($model->hasErrors('username'))
                throw new BadRequestHttpException(null, self::CODE_INCORRECT_USERNAME);
            if ($model->hasErrors('password'))
                throw new BadRequestHttpException(null, self::CODE_INCORRECT_PASSWORD);
            if ($model->hasErrors('status'))
                throw new BadRequestHttpException(null, $model->getErrors('status')[0]);
        }
        throw new BadRequestHttpException('Invalid data');
    }

    public function actionLoginStudent()
    {
        $model = new LoginForm([
            'scenario' => LoginForm::SCENARIO_STUDENT
        ]);
        if ($model->load(Yii::$app->request->post(), '') && $user = $model->login()) {
            $student = Student::findOne(['user_id' => $user->id])->toArray([
                'id', 'name', 'acad'
            ]);
            UserToken::deleteAll(['user_id' => $user->id, 'action' => TokenHelper::TOKEN_ACTION_ACCESS]);
            $userToken = TokenHelper::createUserToken($user->id);
            $student['token'] = $userToken->token;
            return $student;
        } else {
            if ($model->hasErrors('username'))
                throw new BadRequestHttpException(null, self::CODE_INCORRECT_USERNAME);
            if ($model->hasErrors('password'))
                throw new BadRequestHttpException(null, self::CODE_INCORRECT_PASSWORD);
            if ($model->hasErrors('device_hash'))
                throw new BadRequestHttpException(null, self::CODE_INCORRECT_DEVICE);
            if ($model->hasErrors('status'))
                throw new BadRequestHttpException(null, $model->getErrors('status')[0]);
        }
        throw new BadRequestHttpException('Invalid data');
    }

    public function actionSignupStudent()
    {
        $model = new SignupForm([
            'scenario' => SignupForm::SCENARIO_STUDENT
        ]);
        if ($model->load(Yii::$app->request->post(), '') && $user = $model->signup()) {
            $student = $model->getStudent();
            $student->link('user', $user);
            $userToken = TokenHelper::createUserToken($user->id, TokenHelper::TOKEN_ACTION_ACTIVATE_ACCOUNT);
            $this->sendActivationEmail($user, $userToken->token);
            return [
                'token' => $userToken->token
            ];
        }
        throw new BadRequestHttpException('Invalid data');
    }

    public function actionSignupLecturer()
    {
        $model = new SignupForm([
            'scenario' => SignupForm::SCENARIO_LECTURER
        ]);
        if ($model->load(Yii::$app->request->post(), '') && $user = $model->signup()) {
            $lecturer = $model->getLecturer();
            $lecturer->link('user', $user);
            $userToken = TokenHelper::createUserToken($user->id, TokenHelper::TOKEN_ACTION_ACTIVATE_ACCOUNT);
            $this->sendActivationEmail($user, $userToken->token);
            return [
                'token' => $userToken->token
            ];
        }
        throw new BadRequestHttpException('Invalid data');
    }

    private function sendActivationEmail($user, $token)
    {
        Yii::$app->mailer->compose(['html' => '@common/mail/emailConfirmToken-html'], ['user' => $user, 'token' => $token])
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
            ->setTo($user->email)
            ->setSubject('Email confirmation for ' . Yii::$app->name)
            ->send();
    }

    public function actionConfirmEmail($token = null)
    {
        if (empty($token) || !is_string($token)) {
            return $this->redirect(Yii::$app->params['WEB_BASEURL'].'site/confirmation-error');
        }
        $userId = TokenHelper::authenticateToken($token, true, TokenHelper::TOKEN_ACTION_ACTIVATE_ACCOUNT);
        $user = User::findOne([
            'id' => $userId,
            'status' => [User::STATUS_WAIT_EMAIL_DEVICE, User::STATUS_WAIT_EMAIL],
        ]);
        if (!$user)
            return $this->redirect(Yii::$app->params['WEB_BASEURL'].'site/confirmation-error');

        if ($user->status == User::STATUS_WAIT_EMAIL_DEVICE)
            $user->status = User::STATUS_WAIT_DEVICE;
        else if ($user->status == User::STATUS_WAIT_EMAIL)
            $user->status = User::STATUS_ACTIVE;

        UserToken::removeEmailConfirmToken($user->id);
        if ($user->save()) {
            if (YII_ENV === 'test') return 'confirm email successfully';
            return $this->redirect(Yii::$app->params['WEB_BASEURL'].'site/confirmation-success');
        }
        return $this->redirect(Yii::$app->params['WEB_BASEURL'].'site/confirmation-error');
    }

    public function actionRegisterDevice() {
        $model = new RegisterDeviceForm();
        if ($model->load(Yii::$app->request->post(), '') && $user = $model->registerDevice()) {
            UserToken::deleteAll(['user_id' => $user->id, 'action' => TokenHelper::TOKEN_ACTION_ACCESS]);
            return 'register device successfully';
        } else {
            if ($model->hasErrors('username'))
                throw new BadRequestHttpException(null, self::CODE_INCORRECT_USERNAME);
            if ($model->hasErrors('password'))
                throw new BadRequestHttpException(null, self::CODE_INCORRECT_PASSWORD);
            if ($model->hasErrors('device_hash'))
                throw new BadRequestHttpException(null, self::CODE_DUPLICATE_DEVICE);
            if ($model->hasErrors('status'))
                throw new BadRequestHttpException(null, $model->getErrors('status')[0]);
        }
        throw new BadRequestHttpException('Invalid data');
    }

    public function actionLogout() {
        $id = Yii::$app->user->identity->id;
        UserToken::deleteAll(['user_id' => $id, 'action' => TokenHelper::TOKEN_ACTION_ACCESS]);
        return 'logout successfully';
    }

    public function actionChangePassword() {
        $model = new ChangePasswordForm(Yii::$app->user->identity);
        if ($model->load(Yii::$app->request->post(), '') && $model->changePassword())
            return 'change password successfully';
        else {
            if (isset($model->errors['oldPassword']))
                throw new BadRequestHttpException(null, self::CODE_INCORRECT_PASSWORD);
            if (isset($model->errors['newPassword']))
                throw new BadRequestHttpException(null, self::CODE_INVALID_PASSWORD);
        }
        throw new BadRequestHttpException('Invalid data');
    }

    public function actionResetPassword() {
        $model = new ResetPasswordForm();
        if ($model->load(Yii::$app->request->post(), '') && $model->resetPassword()) {
            return 'reset password successfully';
        }
        throw new BadRequestHttpException('Invalid data');
    }

    public function actionAllowTrainFace()
    {
        $bodyParams = Yii::$app->request->post();
        $studentId = $bodyParams['studentId'];
        $student = Student::findOne(['id' => $studentId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');
        $userId = $student->user_id;
        UserToken::deleteAll(['user_id' => $userId, 'action' => TokenHelper::TOKEN_ACTION_TRAIN_FACE]);
        $userToken = TokenHelper::createUserToken($userId, TokenHelper::TOKEN_ACTION_TRAIN_FACE);
        return 'allow training face successfully';
    }

    public function actionDisallowTrainFace()
    {
        $bodyParams = Yii::$app->request->post();
        $studentId = $bodyParams['studentId'];
        $student = Student::findOne(['id' => $studentId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');
        $userId = $student->user_id;
        UserToken::deleteAll(['user_id' => $userId, 'action' => TokenHelper::TOKEN_ACTION_TRAIN_FACE]);
        return 'disable training face successfully';
    }

    private function checkTrainFaceToken($userId) {
        $userToken = UserToken::findOne([
            'action' => TokenHelper::TOKEN_ACTION_TRAIN_FACE,
            'user_id' => $userId,
        ]);
        if (!$userToken) return false;
        $current = time();
        $expire_date = strtotime($userToken->expire_date);
        if ($expire_date < $current) {
            UserToken::deleteAll(['id' => $userToken->id]);
            return false;
        }
        return true;
    }

    public function actionCheckTrainFace() {
        $userId = Yii::$app->user->identity->id;
        return [
            'result' => $this->checkTrainFaceToken($userId),
        ];
    }
}
