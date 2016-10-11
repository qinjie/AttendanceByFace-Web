<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use common\models\Attendance;
use api\modules\v1\controllers\AttendanceController;

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

    public function getAttendanceHistory_ForAClass_InSemester(FunctionalTester $I)
    {
        $I->wantTo('get attendance history for a class in whole semester');
        $I->sendGET('v1/attendance/history', [
            'class_section' => 'T1M2',
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
            $I->assertEquals('T1M2', $item->lesson->class_section);
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

    public function takeAttendanceByFace_Today(FunctionalTester $I)
    {
        $I->wantTo('take attendance in today');
        $attendance = $I->getValidAttendanceToday();
        $I->sendPOST('v1/attendance/face', [
            'id' => $attendance->id,
            'face_id' => '0d3df55d5f5bbfab9d80b7457ecc461d'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'id' => $attendance->id,
            'student_id' => $attendance->student_id,
            'recorded_date' => date('Y-m-d'),
            'recorded_time' => date('H:i')
        ]);
        $I->seeResponseMatchesJsonType([
            'is_absent' => 'integer:<2',
            'is_late' => 'integer:<2',
            'late_min' => 'integer'
        ]);
    }

    public function takeAttendanceByFace_ThrowsException_IfFaceIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('take attendance with invalid face');
        $attendance = $I->getValidAttendanceToday();
        $I->sendPOST('v1/attendance/face', [
            'id' => $attendance->id,
            'face_id' => '123456789'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => AttendanceController::CODE_INVALID_FACE
        ]);
    }

    public function takeAttendanceByFace_ThrowsException_IfAttendanceIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('take attendance with invalid attendance info');
        $I->sendPOST('v1/attendance/face', [
            'id' => 11111,
            'face_id' => '123456789'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => AttendanceController::CODE_INVALID_ATTENDANCE
        ]);
    }
}
