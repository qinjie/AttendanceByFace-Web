<?php

namespace console\controllers;


use common\models\search\LessonSearch;
use common\models\Attendance;

use yii\helpers\Console;

class AttendanceController extends \yii\console\Controller
{
    const SECONDS_IN_DAY = 86400;   // 24 * 60 * 60
    const SECONDS_IN_WEEK = 604800; // 7 * 24 * 60 * 60

    public $fromDate;
    public $toDate;
    public $semester;

    public function options($actionID)
    {
        return ['fromDate', 'toDate', 'semester'];
    }

    public function actionGenerate()
    {
        if (!$this->fromDate || !$this->toDate || !$this->semester) {
            $usage = $this->ansiFormat('./yii attendance/generate --fromDate="YYYY-MM-DD" --toDate="YYYY-MM-DD" --semester="number"', Console::BOLD);
            $this->stdout("\nUsage: $usage\n\n");
            $this->stdout("Generate attendance records from <fromDate> to <toDate>, inclusively\n");
            $this->stdout("based on lessons in <semester> and timetable\n\n");
            $this->stdout("NOTE\n", Console::BOLD);
            $this->stdout("- fromDate should be Monday\n");
            $this->stdout("- toDate should be Sunday\n");
            $this->stdout("- semester should be valid semester in database\n");
            $this->stdout("- meeting pattern will be calculated based on fromDate\n");
            return;
        }
        $semester = intval($this->semester);
        $startTime = strtotime($this->fromDate);
        $endTime = strtotime($this->toDate);

        for ($iterDay = $startTime; $iterDay <= $endTime; $iterDay += self::SECONDS_IN_DAY) {
            $weekday = $this->getWeekday($iterDay);
            $meetingPattern = $this->getMeetingPattern($startTime, $iterDay);

            echo $weekday . " " . $meetingPattern . " " . date('Y-m-d', $iterDay) ."\n";
            $searchLesson = new LessonSearch();
            $searchLesson->semester = $semester;
            $searchLesson->weekday = $weekday;
            $searchLesson->meeting_pattern = $meetingPattern;
            $lessonProvider = $searchLesson->search(null);
            $lessonQuery = $lessonProvider->query;
            $lessonQuery->with('timetables');
            $lessonProvider->pagination = false;
            $lessons = $lessonProvider->getModels();
            foreach ($lessons as $item) {
                foreach ($item->timetables as $timetable) {
                    $attendance = new Attendance();
                    $attendance->student_id = $timetable->student_id;
                    $attendance->lesson_id = $timetable->lesson_id;
                    $attendance->recorded_date = date('Y-m-d', $iterDay);
                    if ($attendance->save())
                        $this->stdout("Insert " . $attendance->student_id . ", " . $attendance->lesson_id . ", " . $attendance->recorded_date. "\n", Console::FG_GREEN);
                    else $this->stdout("Cannot insert " . $attendance->student_id . ", " . $attendance->lesson_id . ", " . $attendance->recorded_date. "\n", Console::FG_RED);
                }
            }
            echo "=============================================\n";
        }
    }

    private function getWeekday($time) {
        $dw = date('w', $time);
        $weekdays = ['SUN', 'MON', 'TUES', 'WED', 'THUR', 'FRI', 'SAT'];
        return $weekdays[$dw];
    }

    private function getMeetingPattern($startTime, $time) {
        $meeting_pattern = '';
        $week = intval(($time - $startTime + self::SECONDS_IN_WEEK) / self::SECONDS_IN_WEEK);
        if ($week % 2 == 0) $meeting_pattern = 'EVEN';
        else $meeting_pattern = 'ODD';
        return $meeting_pattern;
    }

}
