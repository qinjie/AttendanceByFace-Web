<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use common\components\Util;

class TimetableCest
{
    private $accessToken;

    public function _before(FunctionalTester $I)
    {
        $this->accessToken = $I->loginStudent()->token;
        $I->amBearerAuthenticated($this->accessToken);
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function getStudentTimetable_Today(FunctionalTester $I)
    {
        $I->wantTo('get my student timetable of today');
        $I->sendGET('v1/attendance/mine', [
            'expand' => 'lesson'
        ]);
        $I->seeResponseCodeIs(200);
        $userId = $I->grabFromDatabase('user', 'id', [
            'username' => 'canhnht'
        ]);
        $studentId = $I->grabFromDatabase('student', 'id', [
            'user_id' => $userId
        ]);
        $I->seeResponseContainsJson([
            'student_id' => $studentId,
            'recorded_date' => date('Y-m-d'),
            'lesson' => [
                'weekday' => Util::getWeekday(strtotime(date('Y-m-d')))
            ]
        ]);
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'student_id' => 'string',
            'lesson_id' => 'integer',
            'recorded_date' => 'string',
            'lesson' => [
                'id' => 'integer',
                'semester' => 'string',
                'module_id' => 'string',
                'venue_id' => 'integer',
                'weekday' => 'string',
                'start_time' => 'string',
                'end_time' => 'string',
                'meeting_pattern' => 'string'
            ]
        ], '$[*]');
    }

    public function getStudentTimetable_OneDay(FunctionalTester $I)
    {
        $I->wantTo('get my student timetable of an arbitrary day');
        $I->sendGET('v1/attendance/mine', [
            'recorded_date' => '2016-10-12',
            'expand' => 'lesson'
        ]);
        $I->seeResponseCodeIs(200);
        $userId = $I->grabFromDatabase('user', 'id', [
            'username' => 'canhnht'
        ]);
        $studentId = $I->grabFromDatabase('student', 'id', [
            'user_id' => $userId
        ]);
        $I->seeResponseContainsJson([
            'student_id' => $studentId,
            'recorded_date' => '2016-10-12',
            'lesson' => [
                'weekday' => Util::getWeekday(strtotime('2016-10-12'))
            ]
        ]);
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'student_id' => 'string',
            'lesson_id' => 'integer',
            'recorded_date' => 'string',
            'lesson' => [
                'id' => 'integer',
                'semester' => 'string',
                'module_id' => 'string',
                'venue_id' => 'integer',
                'weekday' => 'string',
                'start_time' => 'string',
                'end_time' => 'string',
                'meeting_pattern' => 'string'
            ]
        ], '$[*]');
    }
}
