<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use common\components\Util;

class TimetableStudentCest
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
        $I->sendGET('v1/attendance/day', [
            'expand' => 'lesson'
        ]);
        $I->seeResponseCodeIs(200);
        $userId = $I->grabFromDatabase('user', 'id', [
            'username' => 'canhnht'
        ]);
        $studentId = $I->grabFromDatabase('student', 'id', [
            'user_id' => $userId
        ]);
        $response = json_decode($I->grabResponse());
        foreach ($response as $item) {
            $I->assertEquals($studentId, $item->student_id);
            $I->assertEquals(date('Y-m-d'), $item->recorded_date);
            $I->assertEquals(Util::getWeekday(strtotime(date('Y-m-d'))), $item->lesson->weekday);
        }
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
        $I->sendGET('v1/attendance/day', [
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
        $response = json_decode($I->grabResponse());
        foreach ($response as $item) {
            $I->assertEquals($studentId, $item->student_id);
            $I->assertEquals('2016-10-12', $item->recorded_date);
            $I->assertEquals(Util::getWeekday(strtotime('2016-10-12')), $item->lesson->weekday);
        }
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

    public function getStudentTimetable_CurrentWeek(FunctionalTester $I)
    {
        $I->wantTo('get my student timetable of current week');
        $I->sendGET('v1/attendance/week', [
            'expand' => 'lesson'
        ]);
        $I->seeResponseCodeIs(200);
        $userId = $I->grabFromDatabase('user', 'id', [
            'username' => 'canhnht'
        ]);
        $studentId = $I->grabFromDatabase('student', 'id', [
            'user_id' => $userId
        ]);
        $week = Util::getWeekInSemester(strtotime('2016-10-3'), strtotime(date('Y-m-d')));
        $startDate = Util::getStartDateInWeek(strtotime('2016-10-3'), $week);
        $endDate = Util::getEndDateInWeek(strtotime('2016-10-3'), $week);
        $response = json_decode($I->grabResponse());
        foreach ($response as $item) {
            $I->assertEquals($studentId, $item->student_id);
            $I->assertGreaterThanOrEqual($startDate, $item->recorded_date);
            $I->assertLessThanOrEqual($endDate, $item->recorded_date);
            $I->assertNotEquals('ODD', $item->lesson->meeting_pattern);
        }
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

    public function getStudentTimetable_OneWeek(FunctionalTester $I)
    {
        $I->wantTo('get my student timetable of current week');
        $I->sendGET('v1/attendance/week', [
            'weekNumber' => 1,
            'expand' => 'lesson'
        ]);
        $I->seeResponseCodeIs(200);
        $userId = $I->grabFromDatabase('user', 'id', [
            'username' => 'canhnht'
        ]);
        $studentId = $I->grabFromDatabase('student', 'id', [
            'user_id' => $userId
        ]);
        $startDate = Util::getStartDateInWeek(strtotime('2016-10-3'), 1);
        $endDate = Util::getEndDateInWeek(strtotime('2016-10-3'), 1);
        $response = json_decode($I->grabResponse());
        foreach ($response as $item) {
            $I->assertEquals($studentId, $item->student_id);
            $I->assertGreaterThanOrEqual($startDate, $item->recorded_date);
            $I->assertLessThanOrEqual($endDate, $item->recorded_date);
            $I->assertNotEquals('EVEN', $item->lesson->meeting_pattern);
        }
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
