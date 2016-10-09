<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use api\modules\v1\controllers\UserController;

class LoginStudentCest
{
    const LOGIN_STUDENT_ROUTE = 'v1/student/login';

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function loginStudent_ThrowsException_IfUsernameIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('login as student with invalid username');
        $I->sendPOST(self::LOGIN_STUDENT_ROUTE, [
            'username' => 'student',
            'password' => '123456',
            'device_hash' => 'student'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_INCORRECT_USERNAME,
        ]);
    }

    public function loginStudent_ThrowsException_IfNoUsername(FunctionalTester $I)
    {
        $I->wantTo('login as student with no username');
        $I->sendPOST(self::LOGIN_STUDENT_ROUTE, [
            'password' => '123456'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_INCORRECT_USERNAME,
        ]);
    }

    public function loginStudent_ThrowsException_IfPasswordIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('login as student with invalid password');
        $I->sendPOST(self::LOGIN_STUDENT_ROUTE, [
            'username' => 'canhnht',
            'password' => 'canhnht',
            'device_hash' => 'canhnht'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_INCORRECT_PASSWORD,
        ]);
    }

    public function loginStudent_ThrowsException_IfNoPassword(FunctionalTester $I)
    {
        $I->wantTo('login as student with no password');
        $I->sendPOST(self::LOGIN_STUDENT_ROUTE, [
            'username' => 'canhnht'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_INCORRECT_PASSWORD,
        ]);
    }

    public function loginStudent_ThrowsException_IfDeviceIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('login as student with invalid device');
        $I->sendPOST(self::LOGIN_STUDENT_ROUTE, [
            'username' => 'canhnht',
            'password' => '123456',
            'device_hash' => 'canhnht'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_INCORRECT_DEVICE,
        ]);
    }

    public function loginStudent_ThrowsException_IfNoDevice(FunctionalTester $I)
    {
        $I->wantTo('login as student with no device');
        $I->sendPOST(self::LOGIN_STUDENT_ROUTE, [
            'username' => 'canhnht',
            'password' => '123456'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_INCORRECT_DEVICE,
        ]);
    }

    public function loginStudent_ReturnsToken_IfValidData(FunctionalTester $I)
    {
        $I->wantTo('login as student successfully');
        $deviceHash = $I->grabFromDatabase('user', 'device_hash', [
            'username' => 'canhnht'
        ]);
        $I->sendPOST(self::LOGIN_STUDENT_ROUTE, [
            'username' => 'canhnht',
            'password' => '123456',
            'device_hash' => $deviceHash
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'id' => 'string',
            'name' => 'string',
            'acad' => 'string',
            'token' => 'string'
        ]);
    }
}
