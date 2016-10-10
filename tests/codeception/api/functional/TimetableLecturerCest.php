<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use common\components\Util;

class TimetableLecturerCest
{
    private $accessToken;

    public function _before(FunctionalTester $I)
    {
        $this->accessToken = $I->loginLecturer()->token;
        $I->amBearerAuthenticated($this->accessToken);
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function getLecturerTimetable_Today(FunctionalTester $I)
    {
        $I->wantTo('get my lecturer timetable of today');
        $I->sendGET('v1/attendance/day', [
            'expand' => 'lesson'
        ]);
        $I->seeResponseCodeIs(200);
        $userId = $I->grabFromDatabase('user', 'id', [
            'username' => 'zhangqinjie'
        ]);
        $lecturerId = $I->grabFromDatabase('lecturer', 'id', [
            'user_id' => $userId
        ]);
        $response = json_decode($I->grabResponse());
        foreach ($response as $item) {
            $I->assertEquals($lecturerId, $item->lecturer_id);
            $I->assertEquals(date('Y-m-d'), $item->recorded_date);
            $I->assertEquals(Util::getWeekday(strtotime(date('Y-m-d'))), $item->lesson->weekday);
        }
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'student_id' => 'string',
            'lecturer_id' => 'string',
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

    public function getLecturerTimetable_OneDay(FunctionalTester $I)
    {
        $I->wantTo('get my lecturer timetable of an arbitrary day');
        $I->sendGET('v1/attendance/day', [
            'recorded_date' => '2016-10-12',
            'expand' => 'lesson'
        ]);
        $I->seeResponseCodeIs(200);
        $userId = $I->grabFromDatabase('user', 'id', [
            'username' => 'zhangqinjie'
        ]);
        $lecturerId = $I->grabFromDatabase('lecturer', 'id', [
            'user_id' => $userId
        ]);
        $response = json_decode($I->grabResponse());
        foreach ($response as $item) {
            $I->assertEquals($lecturerId, $item->lecturer_id);
            $I->assertEquals('2016-10-12', $item->recorded_date);
            $I->assertEquals(Util::getWeekday(strtotime('2016-10-12')), $item->lesson->weekday);
        }
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'student_id' => 'string',
            'lecturer_id' => 'string',
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
