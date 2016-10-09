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

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends CustomActiveController
{
    public $modelClass = 'common\models\User';

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
                        'actions' => ['login-lecturer', 'login-student', 'signup-student', 'signup-lecturer', 'reset-password',
                            'register-device', 'confirm-email'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'change-password', 'profile',
                            'change-email', 'change-mobile'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new UnauthorizedHttpException('You are not authorized');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
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

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
