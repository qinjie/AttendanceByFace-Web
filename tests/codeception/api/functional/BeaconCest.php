<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use common\models\User;

class BeaconCest
{
    private $accessToken;
    private $currentLessonId;

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function registerBeacon(FunctionalTester $I)
    {
        $this->accessToken = $I->loginLecturer()->token;
        $I->amBearerAuthenticated($this->accessToken);
        $I->wantTo('register beacon for a class');
        $I->sendPOST('v1/beacon');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'uuid' => 'string',
            'major' => 'integer',
            'minor' => 'integer',
            'user_id' => 'integer',
            'lesson_id' => 'integer'
        ]);
        $response = json_decode($I->grabResponse());
        $this->currentLessonId = $response->lesson_id;
    }

    public function takeAttendance_FirstStudent(FunctionalTester $I)
    {
        $this->accessToken = $I->loginStudent()->token;
        $I->amBearerAuthenticated($this->accessToken);
        $I->wantTo('take attendance by beacon for a class');
        $uuid = $I->grabFromDatabase('beacon', 'uuid', [
            'lesson_id' => $this->currentLessonId
        ]);
        $major = $I->grabFromDatabase('beacon', 'major', [
            'lesson_id' => $this->currentLessonId
        ]);
        $minor = $I->grabFromDatabase('beacon', 'minor', [
            'lesson_id' => $this->currentLessonId
        ]);
        $I->sendPOST('v1/beacon/take-attendance', [
            'uuid' => $uuid,
            'major' => $major,
            'minor' => $minor
        ]);
        $I->seeResponseCodeIs(200);
    }

    public function takeAttendance_SecondStudent(FunctionalTester $I)
    {
        $deviceHash = $I->grabFromDatabase('user', 'device_hash', [
            'username' => 'namth',
            'role' => User::ROLE_STUDENT
        ]);
        $user = [
            'username' => 'namth',
            'password' => '123456',
            'device_hash' => $deviceHash
        ];
        $this->accessToken = $I->loginStudent($user)->token;
        $I->amBearerAuthenticated($this->accessToken);
        $I->wantTo('take attendance by beacon for a class');
        $userId = $I->grabFromDatabase('user', 'id', [
            'username' => 'canhnht',
            'role' => User::ROLE_STUDENT
        ]);
        $uuid = $I->grabFromDatabase('beacon', 'uuid', [
            'lesson_id' => $this->currentLessonId,
            'user_id' => $userId
        ]);
        $major = $I->grabFromDatabase('beacon', 'major', [
            'lesson_id' => $this->currentLessonId,
            'user_id' => $userId
        ]);
        $minor = $I->grabFromDatabase('beacon', 'minor', [
            'lesson_id' => $this->currentLessonId,
            'user_id' => $userId
        ]);
        $I->sendPOST('v1/beacon/take-attendance', [
            'uuid' => $uuid,
            'major' => $major,
            'minor' => $minor
        ]);
        $I->seeResponseCodeIs(200);
    }
}
