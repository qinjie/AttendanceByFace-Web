<?php

namespace common\components;

use common\models\search\LessonSearch;
use common\models\Attendance;

class Util
{
    const SECONDS_IN_DAY = 86400;   // 24 * 60 * 60
    const SECONDS_IN_WEEK = 604800; // 7 * 24 * 60 * 60

    public static function generateAttendance($semester, $startTime, $endTime)
    {
        for ($iterDay = $startTime; $iterDay <= $endTime; $iterDay += self::SECONDS_IN_DAY) {
            $weekday = self::getWeekday($iterDay);
            $meetingPattern = self::getMeetingPattern($startTime, $iterDay);

            // echo $weekday . " " . $meetingPattern . " " . date('Y-m-d', $iterDay) ."\n";
            $searchLesson = new LessonSearch();
            $searchLesson->semester = $semester;
            $searchLesson->weekday = $weekday;
            $searchLesson->meeting_pattern = $meetingPattern;
            $lessonProvider = $searchLesson->search(null);
            $lessonQuery = $lessonProvider->query;
            $lessonQuery->with('timetables');
            $lessonProvider->pagination = false;
            $lessons = $lessonProvider->getModels();
            $transaction = Attendance::getDb()->beginTransaction();
            try {
                foreach ($lessons as $item) {
                    foreach ($item->timetables as $timetable) {
                        $attendance = new Attendance();
                        $attendance->student_id = $timetable->student_id;
                        $attendance->lesson_id = $timetable->lesson_id;
                        $attendance->lecturer_id = $timetable->lecturer_id;
                        $attendance->recorded_date = date('Y-m-d', $iterDay);
                        if ($attendance->save()) {
                            // $this->stdout("Insert " . $attendance->student_id . ", " . $attendance->lesson_id . ", " . $attendance->recorded_date. "\n", Console::FG_GREEN);
                        } else {
                            // $this->stdout("Cannot insert " . $attendance->student_id . ", " . $attendance->lesson_id . ", " . $attendance->recorded_date. "\n", Console::FG_RED);
                        }
                    }
                }
                $transaction->commit();
                // echo "=============================================\n";
            } catch (\Exception $e) {
                $transaction->rollBack();
            }
        }
    }

    public static function getWeekday($time)
    {
        $dw = date('w', $time);
        $weekdays = ['SUN', 'MON', 'TUES', 'WED', 'THUR', 'FRI', 'SAT'];
        return $weekdays[$dw];
    }

    public static function getWeekdayNumber($weekday)
    {
        $weekdayNumber = [
            'MON' => 1,
            'TUES' => 2,
            'WED' => 3,
            'THUR' => 4,
            'FRI' => 5,
            'SAT' => 6,
            'SUN' => 7
        ];
        return $weekdayNumber[$weekday];
    }

    public static function getMeetingPattern($startTime, $time)
    {
        $meeting_pattern = '';
        $week = intval(($time - $startTime + self::SECONDS_IN_WEEK) / self::SECONDS_IN_WEEK);
        if ($week % 2 == 0) $meeting_pattern = 'EVEN';
        else $meeting_pattern = 'ODD';
        return $meeting_pattern;
    }

    public static function getWeekInSemester($startTime, $time)
    {
        $duration = $time - $startTime + self::SECONDS_IN_DAY;
        $week = intval(($duration + self::SECONDS_IN_WEEK - 1) / self::SECONDS_IN_WEEK);
        return $week;
    }

    public static function getMeetingPatternOfWeek($weekNumber)
    {
        $meetingPattern = $weekNumber % 2 == 0 ? 'EVEN' : 'ODD';
        return $meetingPattern;
    }

    public function getStartDateInWeek($startTime, $weekNumber)
    {
        $startDate = date('Y-m-d', $startTime + self::SECONDS_IN_WEEK * ($weekNumber - 1));
        return $startDate;
    }

    public function getEndDateInWeek($startTime, $weekNumber)
    {
        $endDate = date('Y-m-d', $startTime + self::SECONDS_IN_WEEK * $weekNumber - 1);
        return $endDate;
    }

}
