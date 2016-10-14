<?php

namespace api\modules\v1\controllers;

use common\models\Attendance;
use common\models\User;
use common\models\search\AttendanceSearch;
use api\components\CustomActiveController;
use common\components\AccessRule;
use common\components\Util;
use api\models\FaceAttendanceForm;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;
use yii\filters\VerbFilter;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use yii\data\Sort;

/**
 * AttendanceController implements the CRUD actions for Attendance model.
 */
class AttendanceController extends CustomActiveController
{
    public $modelClass = 'common\models\Attendance';

    const CODE_INVALID_FACE = 30;
    const CODE_INVALID_ATTENDANCE = 31;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => HttpBearerAuth::className()
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['day', 'week'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['history', 'face'],
                        'allow' => true,
                        'roles' => [User::ROLE_STUDENT],
                    ],
                    [
                        'actions' => ['semester', 'update'],
                        'allow' => true,
                        'roles' => [User::ROLE_LECTURER],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new UnauthorizedHttpException('You are not authorized');
                },
            ]
        ];
    }

    public function actions() {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['view']);

        return $actions;
    }

    public function actionDay()
    {
        $searchModel = new AttendanceSearch();
        $queryParams = Yii::$app->request->queryParams;
        if (!isset($queryParams['recorded_date']))
            $queryParams['recorded_date'] = date('Y-m-d');
        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->pagination = false;
        $query = $dataProvider->query;
        if (Yii::$app->user->identity->isStudent()) {
            $query->andWhere(['student_id' => Yii::$app->user->identity->student->id]);
        } else if (Yii::$app->user->identity->isLecturer()) {
            $query->andWhere(['lecturer_id' => Yii::$app->user->identity->lecturer->id]);
        }
        $query->joinWith('lesson');
        $query->joinWith('lesson.venue');
        $query->orderBy([
            'lesson.start_time' => SORT_ASC
        ]);
        return $dataProvider;
    }

    public function actionWeek()
    {
        $searchModel = new AttendanceSearch();
        $queryParams = Yii::$app->request->queryParams;
        if (!isset($queryParams['weekNumber']))
            $queryParams['weekNumber'] = Util::getWeekInSemester(strtotime('2016-10-3'), strtotime(date('Y-m-d')));
        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->pagination = false;
        $query = $dataProvider->query;

        if (Yii::$app->user->identity->isStudent()) {
            $query->andWhere(['student_id' => Yii::$app->user->identity->student->id]);
        } else if (Yii::$app->user->identity->isLecturer()) {
            $query->andWhere(['lecturer_id' => Yii::$app->user->identity->lecturer->id]);
        }

        $startDate = Util::getStartDateInWeek(
            strtotime(Attendance::SEMESTER_START_DATE), $queryParams['weekNumber']);
        $endDate = Util::getEndDateInWeek(
            strtotime(Attendance::SEMESTER_START_DATE), $queryParams['weekNumber']);
        $query->andWhere("[[recorded_date]]>='$startDate'");
        $query->andWhere("[[recorded_date]]<='$endDate'");

        $query->joinWith('lesson');
        $query->joinWith('lesson.venue');

        $meeting_pattern = Util::getMeetingPatternOfWeek($queryParams['weekNumber']);
        $query->andWhere(['or',
            ['lesson.meeting_pattern' => $meeting_pattern],
            ['lesson.meeting_pattern' => ''],
        ]);

        $query->orderBy([
            'recorded_date' => SORT_ASC,
            'lesson.start_time' => SORT_ASC
        ]);
        return $dataProvider;
    }

    public function actionSemester()
    {
        $searchModel = new AttendanceSearch();
        $queryParams = Yii::$app->request->queryParams;
        if (!isset($queryParams['fromDate']))
            $queryParams['fromDate'] = date('Y-m-d');
        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->pagination = false;
        $query = $dataProvider->query;

        $query->andWhere(['lecturer_id' => Yii::$app->user->identity->lecturer->id]);
        $query->andWhere("[[recorded_date]]<='{$queryParams['fromDate']}'");

        $query->joinWith('lesson');
        $query->joinWith('student');
        if (isset($queryParams['class_section']))
            $query->andWhere(['lesson.class_section' => $queryParams['class_section']]);

        $query->orderBy([
            'recorded_date' => SORT_DESC,
            'lesson.start_time' => SORT_ASC,
            'lesson.id' => SORT_ASC
        ]);
        return $dataProvider;
    }

    public function actionHistory()
    {
        $searchModel = new AttendanceSearch();
        $queryParams = Yii::$app->request->queryParams;
        if (!isset($queryParams['fromDate']))
            $queryParams['fromDate'] = Attendance::SEMESTER_START_DATE;
        if (!isset($queryParams['toDate']))
            $queryParams['toDate'] = Attendance::SEMESTER_END_DATE;

        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->pagination = false;
        $query = $dataProvider->query;

        $query->andWhere(['student_id' => Yii::$app->user->identity->student->id]);

        $query->andWhere("[[recorded_date]]>='{$queryParams['fromDate']}'");
        $query->andWhere("[[recorded_date]]<='{$queryParams['toDate']}'");

        $query->joinWith('lesson');
        if (isset($queryParams['class_section']))
            $query->andWhere(['lesson.class_section' => $queryParams['class_section']]);

        $query->orderBy([
            'recorded_date' => SORT_ASC,
            'lesson.start_time' => SORT_ASC
        ]);
        return $dataProvider;
    }

    public function actionFace() {
        $model = new FaceAttendanceForm(Yii::$app->user->identity);
        if ($model->load(Yii::$app->request->post(), '')
            && $attendance = $model->takeAttendance()) {
            return $attendance;
        } else {
            if ($model->hasErrors('id'))
                throw new BadRequestHttpException(null, self::CODE_INVALID_ATTENDANCE);
            if ($model->hasErrors('face_id'))
                throw new BadRequestHttpException(null, self::CODE_INVALID_FACE);
        }
    }

    /*
     * Updates an existing Attendance model.
     * If update is successful, the attendance will be returned.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            return $model;
        } else return null;
    }

    /**
     * Finds the Attendance model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Attendance the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Attendance::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
