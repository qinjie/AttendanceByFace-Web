<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use common\models\Attendance;

class AttendanceCest
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

    public function getAttendanceHistory_InSemester(FunctionalTester $I)
    {
        $I->wantTo('get attendance history in whole semester');
        $I->sendGET('v1/attendance/history', [
            'expand' => 'lesson'
        ]);
        $I->seeResponseCodeIs(200);
        $userId = $I->grabFromDatabase('user', 'id', [
            'username' => 'canhnht'
        ]);
        $studentId = $I->grabFromDatabase('student', 'id', [
            'user_id' => $userId
        ]);
        $fromDate = Attendance::SEMESTER_START_DATE;
        $toDate = Attendance::SEMESTER_END_DATE;
        $response = json_decode($I->grabResponse());
        foreach ($response as $item) {
            $I->assertEquals($studentId, $item->student_id);
            $I->assertGreaterThanOrEqual($fromDate, $item->recorded_date);
            $I->assertLessThanOrEqual($toDate, $item->recorded_date);
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
