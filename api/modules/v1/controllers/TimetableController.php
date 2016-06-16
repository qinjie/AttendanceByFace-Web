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

    const STATUS_NOTYET = 0;
    const STATUS_PRESENT = 1;
    const STATUS_LATE = 2;
    const STATUS_ABSENT = 3;

    const ATTENDANCE_INTERVAL = 1500; // 15 minutes
    const FACE_THRESHOLD = 20;

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
                    'actions' => ['today', 'week', 'total-week', 'check-attendance', 'take-attendance'],
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
        $currentDay = date('d');
        $currentMonth = date('m');
        $currentYear = date('Y');
        $weekdays = ['SUN', 'MON', 'TUES', 'WED', 'THUR', 'FRI', 'SAT'];
        $weekday = $weekdays[$dw];

        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');
        $query = Yii::$app->db->createCommand('
            select lesson_id, 
                   subject_area,
                   class_section, 
                   component, 
                   start_time, 
                   end_time, 
                   weekday, 
                   venue.id as venue_id, 
                   venue.location, 
                   venue.name, 
                   timetable.id as timetable_id, 
                   beacon.uuid, 
                   beacon.major, 
                   beacon.minor 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             join venue on lesson.venue_id = venue.id 
             join venue_beacon on venue.id = venue_beacon.venue_id 
             join beacon on venue_beacon.beacon_id = beacon.id 
             where student_id = :student_id 
             and weekday = :weekday 
        ')
        ->bindValue(':student_id', $student->id)
        ->bindValue(':weekday', /*$weekday*/ 'TUES');
        $result = $query->queryAll();

        usort($result, 'self::cmpLesson');

        for ($iter = 0; $iter < count($result); ++$iter) {
            // Tets
            $result[$iter]['weekday'] = $weekday;
            // Test
            
            $status = Yii::$app->db->createCommand('
                    select lesson_id, 
                           student_id,
                           created_at,  
                           is_absent, 
                           is_late  
                     from attendance 
                     where student_id = :student_id 
                     and lesson_id = :lesson_id 
                     and dayofmonth(created_at) = :currentDay 
                     and month(created_at) = :currentMonth 
                     and year(created_at) = :currentYear 
                ')
                ->bindValue(':student_id', $student->id)
                ->bindValue(':lesson_id', $result[$iter]['lesson_id'])
                ->bindValue(':currentDay', $currentDay)
                ->bindValue(':currentMonth', $currentMonth)
                ->bindValue(':currentYear', $currentYear)
                ->queryOne();
            
            if ($status) {
                if ($status['is_absent']) {
                    $result[$iter]['status'] = self::STATUS_ABSENT;
                    $result[$iter]['recorded_at'] = null;
                } else if ($status['is_late']) {
                    $result[$iter]['status'] = self::STATUS_LATE;
                    $time = strtotime($status['created_at']);
                    $result[$iter]['recorded_at'] = date('H:i', $time);
                } else {
                    $result[$iter]['status'] = self::STATUS_PRESENT;
                    $time = strtotime($status['created_at']);
                    $result[$iter]['recorded_at'] = date('H:i', $time);
                }
            } else {
                $result[$iter]['status'] = self::STATUS_NOTYET;
                $result[$iter]['recorded_at'] = null;
            }
        }
        return $result;
    }

    public function actionTotalWeek() {
        $numberWeeks = 5;
        $total_timetable = [];
        for ($iter = 0; $iter < $numberWeeks; ++$iter) {
            $total_timetable[] = self::actionWeek($iter);
        }
        return $total_timetable;
    }

    public function actionWeek($week) {
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');
        $meeting_pattern = ($week % 2 == 0 ? 'EVEN' : 'ODD');
        $query = Yii::$app->db->createCommand('
            select lesson_id, 
                   subject_area, 
                   class_section, 
                   component, 
                   weekday, 
                   start_time, 
                   end_time, 
                   location, 
                   meeting_pattern 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             join venue on lesson.venue_id = venue.id 
             where (meeting_pattern = :meeting_pattern or meeting_pattern = "") 
             and student_id = :student_id
        ')
        ->bindValue(':meeting_pattern', $meeting_pattern)
        ->bindValue(':student_id', $student->id);
        $result = $query->queryAll();
        
        for ($iter = 0; $iter < count($result); ++$iter) {
            $result[$iter]['weekday'] = $this->weekDayToNumber($result[$iter]['weekday']);
        }
        
        usort($result, 'self::cmpLesson');
        
        $week_timetable = [];
        for ($iter = 0; $iter < count($result); ++$iter) {
            $end_iter = $iter;
            $weekday = $this->numberToWeekDay($result[$iter]['weekday']);
            while ($end_iter + 1 < count($result) 
                && $result[$end_iter + 1]['weekday'] == $result[$iter]['weekday']) ++$end_iter;
            $week_timetable[$weekday] = [];
            for (; $iter <= $end_iter; ++$iter) {
                $week_timetable[$weekday][] = $result[$iter];
            }
        }
        return $week_timetable;
    }

    private function numberToWeekDay($number) {
        $result = ['MON', 'TUES', 'WED', 'THUR', 'FRI', 'SAT', 'SUN'];
        return $result[$number];
    }

    private function weekDayToNumber($weekday) {
        $result = [
            'MON' => 0,
            'TUES' => 1,
            'WED' => 2,
            'THUR' => 3,
            'FRI' => 4,
            'SAT' => 5,
            'SUN' => 6,
        ];
        return $result[$weekday];
    }

    private static function cmpTime($t1, $t2) {
        $a1 = explode(':', $t1);
        $a2 = explode(':', $t2);
        $h1 = intval($a1[0]);
        $m1 = intval($a1[1]);

        $h2 = intval($a2[0]);
        $m2 = intval($a2[1]);

        if ($h1 == $h2) {
            if ($m1 == $m2) return 0;
            else return $m1 - $m2;
        } else {
            return $h1 - $h2;
        }
    } 

    private static function cmpLesson($l1, $l2) {
        if ($l1['weekday'] == $l2['weekday']) 
            return self::cmpTime($l1['start_time'], $l2['start_time']);
        else
            return $l1['weekday'] - $l2['weekday'];
    }

    public function actionSemester($semester) {
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');
        $query = Yii::$app->db->createCommand('
            select lesson_id, 
                   subject_area,
                   class_section, 
                   component, 
                   start_time, 
                   end_time, 
                   weekday, 
                   venue.id as venue_id, 
                   venue.location, 
                   venue.name, 
                   timetable.id as timetable_id, 
                   beacon.uuid, 
                   beacon.major, 
                   beacon.minor 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             join venue on lesson.venue_id = venue.id 
             join venue_beacon on venue.id = venue_beacon.venue_id 
             join beacon on venue_beacon.beacon_id = beacon.id 
             where student_id = :student_id 
             and semester = :semester 
             order by start_time
        ')
        ->bindValue(':student_id', $student->id)
        ->bindValue(':semester', $semester);

        $result = $query->queryAll();
        for ($iter = 0; $iter < count($result); ++$iter) {
            $status = Yii::$app->db->createCommand('
                    select lesson_id, 
                           student_id,
                           updated_at,  
                           is_absent, 
                           is_late  
                     from attendance 
                     where student_id = :student_id 
                     and lesson_id = :lesson_id 
                     and dayofmonth(signed_in) = :currentDay 
                     and month(signed_in) = :currentMonth 
                     and year(signed_in) = :currentYear 
                ')
                ->bindValue(':student_id', $student->id)
                ->bindValue(':lesson_id', $result[$iter]['lesson_id'])
                ->bindValue(':currentDay', $currentDay)
                ->bindValue(':currentMonth', $currentMonth)
                ->bindValue(':currentYear', $currentYear)
                ->queryOne();
            if ($status) {
                if ($status['is_absent']) {
                    $result[$iter]['status'] = self::STATUS_ABSENT;
                    $result[$iter]['recorded_at'] = null;
                } else if ($status['is_late']) {
                    $result[$iter]['status'] = self::STATUS_LATE;
                    $result[$iter]['recorded_at'] = $status['updated_at'];
                } else {
                    $result[$iter]['status'] = self::STATUS_PRESENT;
                    $result[$iter]['recorded_at'] = $status['updated_at'];
                }
            } else {
                $result[$iter]['status'] = self::STATUS_NOTYET;
                $result[$iter]['recorded_at'] = null;
            }
        }
        return $result;
    }

    public function actionCheckAttendance() {
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');        
        $request = Yii::$app->request;
        $bodyParams = $request->bodyParams;
        $timetable_id = $bodyParams['timetable_id'];

        $timetable = Yii::$app->db->createCommand('
            select lesson_id, 
                   start_time, 
                   end_time, 
                   timetable.id as timetable_id 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             where student_id = :student_id 
             and timetable.id = :timetable_id 
        ')
        ->bindValue(':student_id', $student->id)
        ->bindValue(':timetable_id', $timetable_id)
        ->queryOne();

        if (!$timetable) {
            throw new BadRequestHttpException('Invalid timetable id');        
        }
        $result = $this->checkTimetable($timetable, $student->id);

        return [
            'result' => $result,
        ];
    }

    private function checkTimetable($timetable, $studentId) {
        date_default_timezone_set("Asia/Singapore");
        $currentTime = date('H:i');

        $attendance = Yii::$app->db->createCommand('
            select lesson_id, 
                   student_id 
             from attendance 
             where student_id = :student_id 
             and lesson_id = :lesson_id 
        ')
        ->bindValue(':student_id', $studentId)
        ->bindValue(':lesson_id', $timetable['lesson_id'])
        ->queryOne();

        $diff = abs(round((strtotime($currentTime) - strtotime($timetable['start_time'])) / 60));
        return $diff <= self::ATTENDANCE_INTERVAL /* && !(bool)$attendance */;
    }

    private function getLateMinutes($timetable) {
        date_default_timezone_set("Asia/Singapore");
        $currentTime = date('H:i');
        $lateMin = round((strtotime($currentTime) - strtotime($timetable['start_time'])) / 60);
        return max($lateMin, 0);
    }

    public function actionTakeAttendance() {
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');        
        $request = Yii::$app->request;
        $bodyParams = $request->bodyParams;
        $timetable_id = $bodyParams['timetable_id'];
        $face_percent = doubleval($bodyParams['face_percent']);

        $timetable = Yii::$app->db->createCommand('
            select lesson_id, 
                   start_time, 
                   end_time, 
                   timetable.id as timetable_id 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             where student_id = :student_id 
             and timetable.id = :timetable_id 
        ')
        ->bindValue(':student_id', $student->id)
        ->bindValue(':timetable_id', $timetable_id)
        ->queryOne();

        if ($face_percent >= self::FACE_THRESHOLD) {
            if ($this->checkTimetable($timetable, $student->id)) {
                $lateMinutes = $this->getLateMinutes($timetable);
                $query = Yii::$app->db->createCommand('
                    insert into attendance  
                     (student_id, lesson_id, is_absent, is_late, late_min) values 
                     (:student_id, :lesson_id, :is_absent, :is_late, :late_min) 
                ')                
                ->bindValue(':student_id', $student->id)
                ->bindValue(':lesson_id', $timetable['lesson_id'])
                ->bindValue(':is_absent', 0)
                ->bindValue(':is_late', intval($lateMinutes > 0))
                ->bindValue(':late_min', $lateMinutes);
                $query->execute();

                date_default_timezone_set("Asia/Singapore");
                return [
                    'is_late' => $lateMinutes > 0,
                    'late_min' => $lateMinutes,
                    'recorded_at' => date('H:i'),
                ];
            } else {
                throw new BadRequestHttpException('Invalid attendance info');
            }
        } else {
            throw new BadRequestHttpException('Face percent must be above 50');
        }
    }

    // public function afterAction($action, $result)
    // {
    //     $result = parent::afterAction($action, $result);
    //     // your custom code here
    //     return [
    //         'status' => 200,
    //         'data' => $result,
    //     ];
    // }
}
