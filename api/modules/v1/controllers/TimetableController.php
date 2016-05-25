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
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

class TimetableController extends CustomActiveController {

    public $modelClass = '';

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
            ],

            'denyCallback' => function ($rule, $action) {
                throw new UnauthorizedHttpException('You are not authorized');
            },
        ];

        return $behaviors;
    }

    public function actionToday() {
        $dw = date('w');
        $weekdays = ['SUN', 'MON', 'TUES', 'WED', 'THUR', 'FRI', 'SAT'];
        $weekday = $weekdays[$dw];
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        $query = Yii::$app->db->createCommand('
            select class_section, component, facility, start_time, end_time, weekday 
             from (timetable join lesson on timetable.lesson_id = lesson.id) 
             join student on timetable.student_id = student.id 
             where timetable.student_id = '.$student->id.'
             and weekday = "'.$weekday.'"
             order by start_time
        ');
        return $query->queryAll();
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
