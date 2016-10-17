<?php

namespace api\modules\v1\controllers;

use common\models\Beacon;
use common\models\Lesson;
use common\models\User;
use common\models\Attendance;
use common\models\search\BeaconSearch;
use api\components\CustomActiveController;
use common\components\AccessRule;
use common\components\Util;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;
use yii\filters\VerbFilter;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use yii\data\Sort;


/**
 * BeaconController implements the CRUD actions for Beacon model.
 */
class BeaconController extends CustomActiveController
{
    public $modelClass = 'common\models\Beacon';

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
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['take-attendance'],
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

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['update'], $actions['view']);
        return $actions;
    }

    private function generateUUID() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    private function generateMajor() {
        return mt_rand( 0, 0xffff );
    }

    private function generateMinor() {
        return mt_rand( 0, 0xffff );
    }

    private function getCurrentLesson() {
        // Will fake with lesson.id = 28
        return Lesson::findOne(['id' => 28]);
        $user = Yii::$app->user->identity;
        $currentTime = date('H:i');
        $currentWeekday = Util::getWeekday(strtotime(date('Y-m-d')));
        $currentMeetingPattern = Util::getMeetingPattern(strtotime('2016-10-03'), strtotime(date('Y-m-d')));
        $query = Lesson::find();
        $query->joinWith('timetables');
        if ($user->isStudent())
            $query->where(['timetable.student_id' => $user->student->id]);
        else
            $query->where(['timetable.lecturer_id' => $user->lecturer->id]);
        $query->andWhere(['weekday' => $currentWeekday]);
        $query->andWhere(['or',
            ['meeting_pattern' => $currentMeetingPattern],
            ['meeting_pattern' => '']
        ]);
        $query->andWhere("[[start_time]]<='$currentTime'");
        $query->andWhere("[[end_time]]>='$currentTime'");
        return $query->one();
    }

    private function generateNewBeacon($uuid, $lessonId) {
        $model = new Beacon();
        $model->uuid = $uuid;
        $model->major = $this->generateMajor();
        $model->minor = $this->generateMinor();
        $model->user_id = Yii::$app->user->identity->id;
        $model->lesson_id = $lessonId;
        if ($model->save()) {
            return $model;
        } else return null;
    }

    /**
     * Creates a new Beacon model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $lesson = $this->getCurrentLesson();
        if (!$lesson) return null;
        $beacon = Beacon::findOne(['lesson_id' => $lesson->id]);
        $uuid = null;
        if ($beacon) $uuid = $beacon->uuid;
        else $uuid = $this->generateUUID();
        if ($beacon = $this->generateNewBeacon($uuid, $lesson->id))
            return $beacon;
        throw new BadRequestHttpException('Cannot generate beacon');
    }

    private function getAttendanceOfLesson($lessonId, $studentId) {
        return Attendance::findOne([
            'lesson_id' => $lessonId,
            'student_id' => $studentId,
            'recorded_date' => date('Y-m-d')
        ]);
    }

    public function actionTakeAttendance()
    {
        // Check more about logic!!!
        $searchModel = new BeaconSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->post());
        $beacon = $dataProvider->getModels()[0];
        $user = User::findOne(['id' => $beacon->user_id]);
        if ($user->isLecturer()) {
            $attendance = $this->getAttendanceOfLesson($beacon->lesson_id, Yii::$app->user->identity->student->id);
            $attendance->is_absent = 0;
            $lateMinutes = Util::getDifferenceInMinutes(
                $attendance->lesson->start_time, date('H:i'));
            $attendance->is_late = intval($lateMinutes > 0);
            $attendance->late_min = $lateMinutes;
            $attendance->recorded_time = date('H:i');
            if ($attendance->save()) {
                return $this->generateNewBeacon($beacon->uuid, $beacon->lesson_id);
            }
        } else if ($user->isStudent()) {
            $userAttendance = $this->getAttendanceOfLesson($beacon->lesson_id, $user->student->id);
            if ($userAttendance && $userAttendance->is_absent === 0) {
                $attendance = $this->getAttendanceOfLesson($beacon->lesson_id, Yii::$app->user->identity->student->id);
                $attendance->is_absent = 0;
                $lateMinutes = Util::getDifferenceInMinutes(
                    $attendance->lesson->start_time, date('H:i'));
                $attendance->is_late = intval($lateMinutes > 0);
                $attendance->late_min = $lateMinutes;
                $attendance->recorded_time = date('H:i');
                if ($attendance->save()) {
                    return $this->generateNewBeacon($beacon->uuid, $beacon->lesson_id);
                }
            }
        }
        throw new BadRequestHttpException('Cannot take attendance');
    }

    /**
     * Finds the Beacon model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Beacon the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Beacon::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
