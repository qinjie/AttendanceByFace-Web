<?php
namespace api\modules\v1\controllers;

use api\common\controllers\CustomActiveController;
use api\common\models\User;
use api\modules\v1\models\Attendance;
use api\common\models\Student;
use api\common\models\Lecturer;
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

    const ATTENDANCE_INTERVAL = 15; // 15 minutes
    const FACE_THRESHOLD = 30;

    const DEFAULT_START_DATE = '2016-06-13';    // Monday
    const DEFAULT_END_DATE = '2016-07-11';  // Sunday, 5 weeks
    const DEFAULT_SEMESTER = 2;
    const TEST_DEFAULT_SEMESTER = 3;

    const SECONDS_IN_DAY = 86400;   // 24 * 60 * 60
    const SECONDS_IN_WEEK = 604800; // 7 * 24 * 60 * 60
    const DAYS_PER_PAGE = 7;

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
                    'actions' => ['today', 'week', 'total-week', 'check-attendance', 
                        'take-attendance', 'next-days', 'one-day', 'take-attendance-beacon'],
                    'allow' => true,
                    'roles' => [User::ROLE_STUDENT],
                ],
                [   
                    'actions' => ['today-for-lecturer', 'one-day-for-lecturer', 'current-semester',
                        'list-student-for-lesson', 'current-week'],
                    'allow' => true,
                    'roles' => [User::ROLE_LECTURER],
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

    private function getMeetingPatternInTime($time) {
        $meeting_pattern = '';
        $t1 = $time;
        $t2 = strtotime(self::DEFAULT_START_DATE);
        $week = intval(($t1 - $t2 + self::SECONDS_IN_WEEK) / self::SECONDS_IN_WEEK);
        if ($week % 2 == 0) $meeting_pattern = 'EVEN';
        else $meeting_pattern = 'ODD';        
        return $meeting_pattern;
    }
    
    public function actionToday() {
        return $this->getTimetableInDate(date('Y-m-d'));
    }

    public function actionOneDay($date) {
        return $this->getTimetableInDate($date);
    }

    private function getTimetableInDate($date) {
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');

        $time = strtotime($date);
        $dw = date('w', $time);
        $currentDay = date('d', $time);
        $currentMonth = date('m', $time);
        $currentYear = date('Y', $time);
        $weekdays = ['SUN', 'MON', 'TUES', 'WED', 'THUR', 'FRI', 'SAT'];
        $weekday = $weekdays[$dw];
        $meeting_pattern = $this->getMeetingPatternInTime($time);

        $result = $this->getAllLessonsInOneDay($student->id, $weekday, 
            self::DEFAULT_SEMESTER, $meeting_pattern);
        usort($result, 'self::cmpLesson');

        for ($iter = 0; $iter < count($result); ++$iter) {
            $statusInfo = $this->getStatusInfo($student->id, $result[$iter],
                $currentDay, $currentMonth, $currentYear);
            $result[$iter]['status'] = $statusInfo['status'];
            $result[$iter]['recorded_at'] = $statusInfo['recorded_at'];
        }
        return $result;
    }

    private function getTimetableInDateForLecturer($date) {
        $userId = Yii::$app->user->identity->id;
        $lecturer = Lecturer::findOne(['user_id' => $userId]);
        if (!$lecturer)
            throw new BadRequestHttpException('No lecturer with given user id');

        $time = strtotime($date);
        $dw = date('w', $time);
        $currentDay = date('d', $time);
        $currentMonth = date('m', $time);
        $currentYear = date('Y', $time);
        $weekdays = ['SUN', 'MON', 'TUES', 'WED', 'THUR', 'FRI', 'SAT'];
        $weekday = $weekdays[$dw];
        $meeting_pattern = $this->getMeetingPatternInTime($time);

        $result = $this->getAllLessonsInOneDayForLecturer($lecturer->id, $weekday, 
            self::DEFAULT_SEMESTER, $meeting_pattern);
        usort($result, 'self::cmpLesson');
        return $result;
    }

    public function actionTodayForLecturer() {
        return $this->getTimetableInDateForLecturer(date('Y-m-d'));
    }

    public function actionOneDayForLecturer($date) {
        return $this->getTimetableInDateForLecturer($date);
    }

    private function getAllLessonsInOneDay($studentId, $weekday, $semester, $meeting_pattern) {
        $query = Yii::$app->db->createCommand('
            select lesson_id, 
                   subject_area,
                   class_section, 
                   component, 
                   start_time, 
                   end_time, 
                   weekday, 
                   meeting_pattern, 
                   venue.id as venue_id, 
                   venue.location, 
                   venue.name, 
                   timetable.id as timetable_id, 
                   beacon.uuid, 
                   beacon.major, 
                   beacon.minor, 
                   lecturer.name as lecturer_name 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             join venue on lesson.venue_id = venue.id 
             join venue_beacon on venue.id = venue_beacon.venue_id 
             join beacon on venue_beacon.beacon_id = beacon.id 
             join lecturer on timetable.lecturer_id = lecturer.id 
             where student_id = :student_id 
             and weekday = :weekday 
             and semester = :semester 
             and (meeting_pattern = \'\' or meeting_pattern = :meeting_pattern) 
        ')
        ->bindValue(':student_id', $studentId)
        ->bindValue(':weekday', $weekday)
        ->bindValue(':semester', $semester)
        ->bindValue(':meeting_pattern', $meeting_pattern);
        return $query->queryAll();
    }

    private function getAllLessonsInOneDayForLecturer($lecturerId, $weekday, $semester, $meeting_pattern) {
        $query = Yii::$app->db->createCommand('
            select lesson_id, 
                   subject_area,
                   class_section, 
                   component, 
                   start_time, 
                   end_time, 
                   weekday, 
                   meeting_pattern, 
                   venue.location, 
                   venue.name, 
                   count(student_id) as number_student 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             join venue on lesson.venue_id = venue.id 
             join lecturer on timetable.lecturer_id = lecturer.id 
             where lecturer_id = :lecturer_id 
             and weekday = :weekday 
             and semester = :semester 
             and (meeting_pattern = \'\' or meeting_pattern = :meeting_pattern) 
             group by lesson_id, subject_area, class_section, component, start_time, 
                      end_time, weekday, meeting_pattern
        ')
        ->bindValue(':lecturer_id', $lecturerId)
        ->bindValue(':weekday', $weekday)
        ->bindValue(':semester', $semester)
        ->bindValue(':meeting_pattern', $meeting_pattern);
        return $query->queryAll();
    }

    private function getStatusInfo($student_id, $lesson, 
        $currentDay, $currentMonth, $currentYear) {
        $status = Yii::$app->db->createCommand('
                select lesson_id, 
                       student_id,
                       recorded_date, 
                       recorded_time, 
                       is_absent, 
                       is_late  
                 from attendance 
                 where student_id = :student_id 
                 and lesson_id = :lesson_id 
                 and dayofmonth(recorded_date) = :currentDay 
                 and month(recorded_date) = :currentMonth 
                 and year(recorded_date) = :currentYear 
            ')
            ->bindValue(':student_id', $student_id)
            ->bindValue(':lesson_id', $lesson['lesson_id'])
            ->bindValue(':currentDay', $currentDay)
            ->bindValue(':currentMonth', $currentMonth)
            ->bindValue(':currentYear', $currentYear)
            ->queryOne();
        
        $result = [];
        if ($status) {
            if ($status['is_absent']) {
                $result['status'] = self::STATUS_ABSENT;
                $result['recorded_at'] = null;
            } else if ($status['is_late']) {
                $result['status'] = self::STATUS_LATE;
                $result['recorded_at'] = $status['recorded_time'];
            } else {
                $result['status'] = self::STATUS_PRESENT;
                $result['recorded_at'] = $status['recorded_time'];
            }
        } else {
            $result['status'] = self::STATUS_NOTYET;
            $result['recorded_at'] = null;
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
                     and dayofmonth(updated_at) = :currentDay 
                     and month(updated_at) = :currentMonth 
                     and year(updated_at) = :currentYear 
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

    private function getTodayMeetingPattern() {
        $meeting_pattern = '';
        $t1 = strtotime(date('Y-m-d'));
        $t2 = strtotime(self::DEFAULT_START_DATE);
        $week = intval(($t1 - $t2 + self::SECONDS_IN_WEEK - 1) / self::SECONDS_IN_WEEK);
        if ($week % 2 == 0) $meeting_pattern = 'EVEN';
        else $meeting_pattern = 'ODD';        
        return $meeting_pattern;
    }

    public function actionCheckAttendance() {
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');        
        $request = Yii::$app->request;
        $bodyParams = $request->bodyParams;
        $timetable_id = $bodyParams['timetable_id'];

        $response = [];
        $result = $this->checkTimetable($student->id, $timetable_id);
        if ($result['ok']) $response['result'] = 0;
        else {
            $timetable = $result['timetable'];
            $currentTime = strtotime(date('H:i'));
            $startTime = strtotime($timetable['start_time']);
            if ($currentTime < $startTime) {
                $response['result'] = -1;
                $response['waitTime'] = $startTime - $currentTime - self::ATTENDANCE_INTERVAL * 60;
            } else {
                $response['result'] = 1;
                $response['lateTime'] = $currentTime - $startTime - self::ATTENDANCE_INTERVAL * 60;
            }
        }

        $response['currentTime'] = date('H:i');
        return $response;
    }

    private function getTimetableById($studentId, $timetableId) {
        $dw = date('w');
        $weekdays = ['SUN', 'MON', 'TUES', 'WED', 'THUR', 'FRI', 'SAT'];
        $weekday = $weekdays[$dw];
        $meeting_pattern = $this->getTodayMeetingPattern();

        $timetable = Yii::$app->db->createCommand('
            select lesson_id, 
                   start_time, 
                   end_time, 
                   timetable.id as timetable_id 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             where student_id = :student_id 
             and timetable.id = :timetable_id 
             and weekday = :weekday 
             and (meeting_pattern = \'\' or meeting_pattern = :meeting_pattern) 
        ')
        ->bindValue(':student_id', $studentId)
        ->bindValue(':timetable_id', $timetableId)
        ->bindValue(':weekday', $weekday)
        ->bindValue(':meeting_pattern', $meeting_pattern)
        ->queryOne();

        return $timetable;
    }

    private function checkTimetable($studentId, $timetableId) {
        $timetable = $this->getTimetableById($studentId, $timetableId);
        if (!$timetable) {
            throw new BadRequestHttpException('Invalid timetable id');        
        }

        $currentDay = date('d');
        $currentMonth = date('m');
        $currentYear = date('Y');
        $attendance = Yii::$app->db->createCommand('
            select lesson_id, 
                   student_id 
             from attendance 
             where student_id = :student_id 
             and lesson_id = :lesson_id 
             and year(recorded_date) = :currentYear 
             and month(recorded_date) = :currentMonth 
             and day(recorded_date) = :currentDay 
        ')
        ->bindValue(':student_id', $studentId)
        ->bindValue(':lesson_id', $timetable['lesson_id'])
        ->bindValue(':currentYear', $currentYear)
        ->bindValue(':currentMonth', $currentMonth)
        ->bindValue(':currentDay', $currentDay)
        ->queryOne();

        $currentTime = strtotime(date('H:i'));
        $startTime = strtotime($timetable['start_time']);
        $diff = abs(round($currentTime - $startTime) / 60);
        $result = [
            'timetable' => $timetable,
            'ok' => false,
        ];
        if ($diff <= self::ATTENDANCE_INTERVAL && !(bool)$attendance) $result['ok'] = true;
        return $result;
    }

    private function getLateMinutes($timetable) {
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

        $checkResult = $this->checkTimetable($student->id, $timetable_id);
        $timetable = $checkResult['timetable'];
        $ok = $checkResult['ok'];

        if ($face_percent >= self::FACE_THRESHOLD) {
            if ($ok) {
                $lateMinutes = $this->getLateMinutes($timetable);
                $attendance = new Attendance();
                $attendance->student_id = $student->id;
                $attendance->lesson_id = $timetable['lesson_id'];
                $attendance->is_absent = 0;
                $attendance->is_late = intval($lateMinutes > 0);
                $attendance->late_min = $lateMinutes;
                $currentTime = date('H:i');
                $currentDate = date('Y-m-d');
                $attendance->recorded_time = $currentTime;
                $attendance->recorded_date = $currentDate;

                if ($attendance->save()) {
                    return [
                        'is_late' => $lateMinutes > 0,
                        'late_min' => $lateMinutes,
                        'recorded_at' => $currentTime,
                    ];
                } else {
                    throw new BadRequestHttpException('Cannot insert new attendance');
                }
            } else {
                throw new BadRequestHttpException('Invalid attendance info');
            }
        } else {
            throw new BadRequestHttpException('Face percent must be above '.self::FACE_THRESHOLD);
        }
    }

    public function actionTakeAttendanceBeacon() {
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');
        $request = Yii::$app->request;
        $bodyParams = $request->bodyParams;
        $timetable_id = $bodyParams['timetable_id'];

        $checkResult = $this->checkTimetable($student->id, $timetable_id);
        $timetable = $checkResult['timetable'];
        $ok = $checkResult['ok'];

        if ($ok) {
            $lateMinutes = $this->getLateMinutes($timetable);
            $attendance = new Attendance();
            $attendance->student_id = $student->id;
            $attendance->lesson_id = $timetable['lesson_id'];
            $attendance->is_absent = 0;
            $attendance->is_late = intval($lateMinutes > 0);
            $attendance->late_min = $lateMinutes;
            $currentTime = date('H:i');
            $currentDate = date('Y-m-d');
            $attendance->recorded_time = $currentTime;
            $attendance->recorded_date = $currentDate;

            if ($attendance->save()) {
                return [
                    'is_late' => $lateMinutes > 0,
                    'late_min' => $lateMinutes,
                    'recorded_at' => $currentTime,
                ];
            } else {
                throw new BadRequestHttpException('Cannot insert new attendance');
            }
        } else {
            throw new BadRequestHttpException('Invalid attendance info');
        }
    }

    public function actionNextDays($days = 1) {
        $userId = Yii::$app->user->identity->id;
        $student = Student::findOne(['user_id' => $userId]);
        if (!$student)
            throw new BadRequestHttpException('No student with given user id');        

        $currentTime = strtotime(date('Y-m-d'));
        $listLesson = $this->getAllLessonsOfStudent($student->id);
        $listAttendance = [];
        for ($iter_day = 0; $iter_day < $days; ++$iter_day) {
            $time = $currentTime + $iter_day * self::SECONDS_IN_DAY;
            $dw = date('w', $time);
            $weekdays = ['SUN', 'MON', 'TUES', 'WED', 'THUR', 'FRI', 'SAT'];
            $weekday = $weekdays[$dw];
            $listLessonInDay = $this->getLessonsInWeekday($listLesson, $weekday);
            $listAttendance[date('Y-m-d', $time)] = $listLessonInDay;
        }
        return $listAttendance;
    }

    private function getLessonsInWeekday($listLesson, $weekday) {
        $result = [];
        for ($iter = 0; $iter < count($listLesson); ++$iter) {
            if ($listLesson[$iter]['weekday'] == $weekday)
                $result[] = $listLesson[$iter];
        }
        usort($result, 'self::cmpLesson');
        return $result;
    }

    private function getAllLessonsOfStudent($student_id) {
        $listLesson = Yii::$app->db->createCommand('
            select lesson_id, 
                   start_time, 
                   end_time, 
                   class_section, 
                   component, 
                   subject_area, 
                   meeting_pattern, 
                   weekday, 
                   location, 
                   lecturer.name as lecturer_name 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             join venue on lesson.venue_id = venue.id 
             join lecturer on timetable.lecturer_id = lecturer.id 
             where student_id = :student_id 
             and semester = :semester 
        ')
        ->bindValue(':student_id', $student_id)
        ->bindValue(':semester', self::DEFAULT_SEMESTER)
        ->queryAll();

        return $listLesson;
    }

    public function actionCurrentSemester($fromDate, $classSection) {
        // if (!$fromDate) $fromDate = date('Y-m-d');
        $userId = Yii::$app->user->identity->id;
        $lecturer = Lecturer::findOne(['user_id' => $userId]);
        if (!$lecturer)
            throw new BadRequestHttpException('No lecturer with given user id');
        
        $start_time = strtotime($fromDate);
        $end_time = max(strtotime(self::DEFAULT_START_DATE), $start_time - (self::DAYS_PER_PAGE - 1) * self::SECONDS_IN_DAY);

        $listLesson = $this->getAllLessonsInSemester($lecturer->id, self::TEST_DEFAULT_SEMESTER);
        if ($classSection !== 'all') {
            $filteredListLesson = [];
            for ($iter = 0; $iter < count($listLesson); ++$iter) {
                if ($listLesson[$iter]['class_section'] == $classSection) {
                    $filteredListLesson[] = $listLesson[$iter];
                }
            }
            $listLesson = $filteredListLesson;
        }
        
        $result = [];
        for ($iter_time = $start_time; $iter_time >= $end_time; $iter_time -= self::SECONDS_IN_DAY) {
            $meeting_pattern = $this->getMeetingPatternInTime($iter_time);
            $dw = date('w', $iter_time);
            $weekdays = ['SUN', 'MON', 'TUES', 'WED', 'THUR', 'FRI', 'SAT'];
            $weekday = $weekdays[$dw];
            for ($iter = 0; $iter < count($listLesson); ++$iter) {
                $lesson = $listLesson[$iter];
                $listStudent = $this->getListStudentOfLesson($lesson['lesson_id'], $iter_time);
                // return $listStudent;
                if ($lesson['weekday'] == $weekday 
                    && ($lesson['meeting_pattern'] == '' || $lesson['meeting_pattern'] == $meeting_pattern)) {
                    $result[] = $lesson;
                    $id = count($result) - 1;
                    $result[$id]['date'] = date('Y-m-d', $iter_time);
                    $result[$id]['totalStudent'] = count($listStudent);
                    $result[$id]['presentStudent'] = $this->getNumberPresentStudent($listStudent);
                }
            }
        }
        usort($result, 'self::cmpLessonInTimetable');
        return [
            'timetable' => $result,
            'nextFromDate' => date('Y-m-d', $end_time - self::SECONDS_IN_DAY)
        ];
    }

    private function getNumberPresentStudent($listStudent) {
        $result = 0;
        for ($iter = 0; $iter < count($listStudent); ++$iter) {
            if ($listStudent[$iter]['attendance_id'] != null
                && !$listStudent[$iter]['is_absent']) {
                ++$result;
            }
        }
        return $result;
    }

    private function getListStudentOfLesson($lessonId, $time) {
        $currentDay = date('d', $time);
        $currentMonth = date('m', $time);
        $currentYear = date('Y', $time);

        $listStudent = Yii::$app->db->createCommand('
            select timetable.student_id, 
                   attendance.id as attendance_id, 
                   timetable.lesson_id, 
                   is_absent, 
                   is_late, 
                   recorded_date 
             from timetable left join attendance 
             on timetable.student_id = attendance.student_id and timetable.lesson_id = attendance.lesson_id 
             and dayofmonth(recorded_date) = :currentDay 
             and month(recorded_date) = :currentMonth 
             and year(recorded_date) = :currentYear 
             where timetable.lesson_id = :lessonId 
        ')
        ->bindValue(':lessonId', $lessonId)
        ->bindValue(':currentDay', $currentDay)
        ->bindValue(':currentMonth', $currentMonth)
        ->bindValue(':currentYear', $currentYear)
        ->queryAll();

        return $listStudent;
    }

    private static function cmpLessonInTimetable($a1, $a2) {
        $cmpDate = strcmp($a1['date'], $a2['date']);
        if ($cmpDate != 0) return -$cmpDate;
        else return -self::cmpTime($a1['start_time'], $a2['start_time']);
    }

    private function getAllLessonsInSemester($lecturerId, $semester) {
        $listLesson = Yii::$app->db->createCommand('
            select class_section, 
                   lesson_id, 
                   subject_area, 
                   weekday, 
                   meeting_pattern, 
                   component, 
                   semester, 
                   start_time, 
                   end_time, 
                   lecturer.name as lecturer_name, 
                   venue.location, 
                   venue.name 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             join lecturer on timetable.lecturer_id = lecturer.id 
             join venue on lesson.venue_id = venue.id 
             where semester = :semester 
             and lecturer_id = :lecturerId 
             group by class_section, lesson_id, weekday, meeting_pattern, 
                component, semester, start_time, end_time
        ')
        ->bindValue(':semester', $semester)
        ->bindValue(':lecturerId', $lecturerId)
        ->queryAll();

        return $listLesson;
    }

    public function actionListStudentForLesson($lessonId, $date) {
        $time = strtotime($date);
        $currentDay = date('d', $time);
        $currentMonth = date('m', $time);
        $currentYear = date('Y', $time);

        $listStudent = Yii::$app->db->createCommand('
            select s1.name as student_name,
                   s1.id as student_id,
                   t1.lesson_id, 
                   a1.id as attendance_id, 
                   (select count(attendance.id) 
                    from attendance 
                    where attendance.student_id = t1.student_id and attendance.lesson_id = t1.lesson_id
                    and attendance.is_absent = 1) as countAbsent,
                   (select count(attendance.id)
                    from attendance
                    where attendance.student_id = t1.student_id and attendance.lesson_id = t1.lesson_id
                    and attendance.is_absent = 0 and attendance.is_late = 0) as countPresent,
                   (select count(attendance.id)
                    from attendance
                    where attendance.student_id = t1.student_id and attendance.lesson_id = t1.lesson_id
                    and attendance.is_absent = 0 and attendance.is_late = 1) as countLate,
                   a1.is_absent,
                   a1.is_late
             from timetable as t1 join student as s1 on t1.student_id = s1.id
             left join attendance as a1 on t1.lesson_id = a1.lesson_id and t1.student_id = a1.student_id
             and dayofmonth(a1.recorded_date) = :currentDay 
             and month(a1.recorded_date) = :currentMonth 
             and year(a1.recorded_date) = :currentYear 
             where t1.lesson_id = :lessonId
        ')
        ->bindValue(':lessonId', $lessonId)
        ->bindValue(':currentDay', $currentDay)
        ->bindValue(':currentMonth', $currentMonth)
        ->bindValue(':currentYear', $currentYear)        
        ->queryAll();

        // return $listStudent;

        $func = function($val) {
            $newVal = [
                'student_id' => $val['student_id'],
                'student_name' => $val['student_name'],
                'status' => self::STATUS_NOTYET,
                'countAbsent' => $val['countAbsent'],
                'countLate' => $val['countLate'],
                'countPresent' => $val['countPresent'],
            ];
            if ($val['attendance_id'] != null) {
                if ($val['is_absent']) $newVal['status'] = self::STATUS_ABSENT;
                else if ($val['is_late']) $newVal['status'] = self::STATUS_LATE;
                else $newVal['status'] = self::STATUS_PRESENT;
            }
            return $newVal;
        };
        $listStudent = array_map($func, $listStudent);
        return $listStudent;           
    }

    public function actionCurrentWeek() {
        $userId = Yii::$app->user->identity->id;
        $lecturer = Lecturer::findOne(['user_id' => $userId]);
        if(!$lecturer)
            throw new BadRequestHttpException('No lecturer with given user id');

        $duration = strtotime(date('Y-m-d')) - strtotime(self::DEFAULT_START_DATE) + self::SECONDS_IN_DAY;
        $currentWeek = intval(($duration + self::SECONDS_IN_WEEK - 1) / self::SECONDS_IN_WEEK);
        $meetingPattern = $currentWeek % 2 == 0 ? 'EVEN' : 'ODD';
        
        $listLesson = Yii::$app->db->createCommand('
            select class_section, 
                   lesson_id, 
                   subject_area, 
                   weekday, 
                   meeting_pattern, 
                   component, 
                   semester, 
                   start_time, 
                   end_time, 
                   lecturer.name as lecturer_name, 
                   venue.location, 
                   venue.name 
             from timetable join lesson on timetable.lesson_id = lesson.id 
             join lecturer on timetable.lecturer_id = lecturer.id 
             join venue on lesson.venue_id = venue.id 
             where semester = :semester 
             and lecturer_id = :lecturerId 
             and (meeting_pattern = \'\' or meeting_pattern = :meetingPattern) 
             group by class_section, lesson_id, weekday, meeting_pattern, 
                component, semester, start_time, end_time
        ')
        ->bindValue(':semester', self::TEST_DEFAULT_SEMESTER)
        ->bindValue(':lecturerId', $lecturer->id)
        ->bindValue(':meetingPattern', $meetingPattern)
        ->queryAll();

        usort($listLesson, 'self::cmpLessonInWeek');
        return $listLesson;
    }

    private static function cmpLessonInWeek($l1, $l2) {
        if ($l1['weekday'] == $l2['weekday']) 
            return self::cmpTime($l1['start_time'], $l2['start_time']);
        else
            return self::weekDayToNumber($l1['weekday']) - self::weekDayToNumber($l2['weekday']);
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
