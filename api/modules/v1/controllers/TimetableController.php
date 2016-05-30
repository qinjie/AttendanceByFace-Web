<?php
namespace api\modules\v1\controllers;

use api\common\controllers\CustomActiveController;
use api\common\models\User;
use api\modules\v1\models\Timetable;
use api\common\models\Student;
use api\common\components\AccessRule;

use yii\rest\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

class TimetableController extends CustomActiveController {

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
                    'actions' => ['today'],
                    'allow' => true,
                    'roles' => [User::ROLE_STUDENT],
                ],
                [   
                    'actions' => ['take-attendance'],
                    'allow' => true,
                    'roles' => [User::ROLE_STUDENT],
                ],
            ],

            'denyCallback' => function ($rule, $action) {
                throw new UnauthorizedHttpException('You are not authorized');
            },
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'take-attendance' => ['post'],
            ],
        ];

        return $behaviors;
    }

    public function actionToday() {
        $dw = date('w');
        $weekdays = ['SUN', 'MON', 'TUES', 'WED', 'THUR', 'FRI', 'SAT'];
        $weekday = $weekdays[$dw];
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');
        $query = Yii::$app->db->createCommand('
            select lesson_id, 
                   class_section, 
                   component, 
                   facility, 
                   start_time, 
                   end_time, 
                   weekday,
                   venue_id,
                   venue.location,
                   venue.name,
                   timetable.id as timetable_id 
             from ((timetable join lesson on timetable.lesson_id = lesson.id) 
             join student on timetable.student_id = student.id)
             join venue on lesson.venue_id = venue.id  
             where timetable.student_id = :student_id 
             and weekday = :weekday 
             order by start_time
        ')
        ->bindValue(':student_id', $student->id)
        ->bindValue(':weekday', $weekday);
        return $query->queryAll();
    }

    public function actionTakeAttendance() {
        $userId = Yii::$app->user->identity->id;
        $request = Yii::$app->request;
        $bodyParams = $request->bodyParams;
        $timetable_id = $bodyParams['timetable_id'];
        return 'take attendance successfully';

        // $query = Yii::$app->db->createCommand('
        //     select venue.id as venue_id,
        //            beacon_id 
        //      from timetable join lesson on timetable.lesson_id = lesson.id
        //      join venue on lesson.venue_id = venue.id
        //      join venue_beacon on venue.id = venue_beacon.venue_id
        //      join beacon on beacon.id = venue_beacon.beacon_id
        //      join student on timetable.student_id = student.id
        //      where beacon.major = :major and 
        //            beacon.minor = :minor and 
        //            beacon.uuid = :uuid and 
        //            timetable.id = :timetable_id and 
        //            student.user_id = :user_id 
        // ')
        // ->bindValue(':major', $major)
        // ->bindValue(':minor', $minor)
        // ->bindValue(':uuid', $uuid)
        // ->bindValue(':timetable_id', $timetable_id)
        // ->bindValue(':user_id', $userId);
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        // your custom code here
        return [
            'status' => 200,
            'data' => $result,
        ];
    }
}
