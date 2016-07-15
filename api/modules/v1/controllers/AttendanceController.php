<?php
namespace api\modules\v1\controllers;

use api\common\controllers\CustomActiveController;
use api\common\models\User;
use api\modules\v1\models\Attendance;
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

class AttendanceController extends CustomActiveController {

    const STATUS_NOTYET = 0;
    const STATUS_PRESENT = 1;
    const STATUS_LATE = 2;
    const STATUS_ABSENT = 3;

    const ATTENDANCE_INTERVAL = 15; // 15 minutes
    const FACE_THRESHOLD = 30;

    const DEFAULT_START_DATE = '2016-06-13';    // Monday
    const DEFAULT_END_DATE = '2016-07-11';  // Sunday, 5 weeks

    const SECONDS_IN_DAY = 86400;

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
                    'actions' => ['list-semester', 'attendance-history', 
                        'list-class-section'],
                    'allow' => true,
                    'roles' => [User::ROLE_STUDENT],
                ]
            ],

            'denyCallback' => function ($rule, $action) {
                throw new UnauthorizedHttpException('You are not authorized');
            },
        ];

        return $behaviors;
    }
    
    public function actionListSemester() {
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');
        $listSemester = Yii::$app->db->createCommand('
            select distinct semester 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             where student_id = :studentId 
        ')
        ->bindValue(':studentId', $student->id)
        ->queryAll();

        $func = function($val) {
            return $val['semester'];
        };
        $listSemester = array_map($func, $listSemester);
        sort($listSemester);
        return $listSemester;
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

    public function actionAttendanceHistory($semester = '', $class_section = null, 
        $status = null, $start_date = null, $end_date = null) {
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');

        $start_time = $end_time = 0;
        if (!$start_date)
            $start_time = strtotime(self::DEFAULT_START_DATE);
        else
            $start_time = strtotime($start_date);
        if (!$end_date)
            $end_time = strtotime(self::DEFAULT_END_DATE);
        else
            $end_time = strtotime($end_date);
        if (!$class_section) {
            $class_section = $this->getAllClassSections($student->id, $semester);
        } else {
            $class_section = array($class_section);
        }
        
        $result = [];
        for ($iter = 0; $iter < count($class_section); ++$iter) {
            $listLesson = $this->getAllLessonsOfClass($semester, $class_section[$iter]);
            $result[$class_section[$iter]] = $this->getAttendanceHistoryForClass($student->id, 
                $semester, $class_section[$iter], $listLesson, $start_time, $end_time);
        }
        return (object)$result;
    }

    private function getAllLessonsOfClass($semester = '', $class_section) {
        $listLesson = Yii::$app->db->createCommand('
            select class_section, 
                   lesson.id as lesson_id, 
                   weekday, 
                   meeting_pattern, 
                   component, 
                   semester, 
                   start_time, 
                   end_time 
             from lesson 
             where class_section = :class_section 
             and semester = :semester 
        ')
        ->bindValue(':class_section', $class_section)
        ->bindValue(':semester', $semester)
        ->queryAll();

        return $listLesson;
    }

    private function getAttendanceHistoryForClass($student_id, $semester, $class_section, 
        $listLesson, $start_time, $end_time) {
        $start_date = date('Y-m-d', $start_time);
        $end_date = date('Y-m-d', $end_time);
        $listAttendance = Yii::$app->db->createCommand('
            select date(attendance.updated_at) as date, 
                   class_section, 
                   component, 
                   semester, 
                   is_absent, 
                   is_late, 
                   late_min, 
                   lesson_id, 
                   weekday, 
                   start_time, 
                   end_time 
             from attendance join lesson on attendance.lesson_id = lesson.id 
             where student_id = :student_id 
             and class_section = :class_section 
             and attendance.updated_at >= :start_date 
             and attendance.updated_at <= :end_date 
             and semester = :semester 
             order by attendance.updated_at
        ')
        ->bindValue(':student_id', $student_id)
        ->bindValue(':class_section', $class_section)
        ->bindValue(':start_date', $start_date)
        ->bindValue(':end_date', $end_date)
        ->bindValue(':semester', $semester)
        ->queryAll();
        
        $attendanceHistory = [];
        // For each week
        $count = 0;
        for ($iter_week = $start_time; $iter_week <= $end_time; $iter_week += self::SECONDS_IN_DAY * 7) {
            ++$count;
            
            for ($iter = 0; $iter < count($listLesson); ++$iter) {
                $lesson = $listLesson[$iter];
                $lessonId = $lesson['lesson_id'];
                $meeting_pattern = $lesson['meeting_pattern'];
                if ($meeting_pattern == 'ODD' && $count % 2 == 0) continue;
                if ($meeting_pattern == 'EVEN' && $count % 2 == 1) continue;
                $numberInWeek = $this->weekDayToNumber($lesson['weekday']);
                $currentDate = date('Y-m-d', $iter_week + self::SECONDS_IN_DAY * $numberInWeek);
                
                $foundAttendance = $this->getAttendanceInDate($listAttendance, $currentDate, $lessonId);
                $attendance = [];
                if ($foundAttendance) {
                    $attendance['date'] = $foundAttendance['date'];
                    $attendance['lesson_id'] = $foundAttendance['lesson_id'];
                    $attendance['class_section'] = $foundAttendance['class_section'];
                    $attendance['component'] = $foundAttendance['component'];
                    $attendance['semester'] = $foundAttendance['semester'];
                    $attendance['weekday'] = $foundAttendance['weekday'];
                    $attendance['start_time'] = $foundAttendance['start_time'];
                    $attendance['end_time'] = $foundAttendance['end_time'];
                    $status = self::STATUS_PRESENT;
                    if ($foundAttendance['is_absent'])
                        $status = self::STATUS_ABSENT;
                    else if ($foundAttendance['is_late'])
                        $status = self::STATUS_LATE;
                    $attendance['status'] = $status;
                } else {
                    $attendance['date'] = $currentDate;
                    $attendance['lesson_id'] = $lesson['lesson_id'];
                    $attendance['class_section'] = $lesson['class_section'];
                    $attendance['component'] = $lesson['component'];
                    $attendance['semester'] = $lesson['semester'];
                    $attendance['weekday'] = $lesson['weekday'];
                    $attendance['status'] = self::STATUS_NOTYET;
                    $attendance['start_time'] = $lesson['start_time'];
                    $attendance['end_time'] = $lesson['end_time'];
                }
                $attendanceHistory[] = $attendance;
            }
        }
        return $attendanceHistory;
    }

    private function getAttendanceInDate($listAttendance, $date, $lessonId) {
        $result = null;
        for ($iter = 0; $iter < count($listAttendance); ++$iter) {
            if ($listAttendance[$iter]['date'] == $date 
                && $listAttendance[$iter]['lesson_id'] == $lessonId) {
                $result = $listAttendance[$iter];
                break;
            }
        }
        return $result;
    }

    private function getAllClassSections($student_id, $semester = '') {
        $listClassSections = Yii::$app->db->createCommand('
            select distinct class_section 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             where student_id = :student_id 
             and semester = :semester
        ')
        ->bindValue(':student_id', $student_id)
        ->bindValue(':semester', $semester)
        ->queryAll();

        $func = function($val) {
            return $val['class_section'];
        };
        $listClassSections = array_map($func, $listClassSections);
        return $listClassSections;
    }

    public function actionListClassSection($semester = '') {
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');

        return $this->getAllClassSections($student->id, $semester);
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
