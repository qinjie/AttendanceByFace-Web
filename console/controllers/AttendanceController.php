<?php

namespace console\controllers;


use common\models\search\LessonSearch;
use common\models\Attendance;
use common\components\Util;

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

        Util::generateAttendance($semester, $startTime, $endTime);
    }
}
