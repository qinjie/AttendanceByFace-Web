<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use api\modules\v1\controllers\UserController;

class LoginLecturerCest
{
    const LOGIN_LECTURER_ROUTE = 'lecturer/login';

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function loginLecturer_ThrowsException_IfUsernameIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('login as lecturer with invalid username');
        $I->sendPOST(self::LOGIN_LECTURER_ROUTE, [
            'username' => 'lecturer',
            'password' => '123456'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_INCORRECT_USERNAME,
        ]);
    }

    public function loginLecturer_ThrowsException_IfNoUsername(FunctionalTester $I)
    {
        $I->wantTo('login as lecturer with no username');
        $I->sendPOST(self::LOGIN_LECTURER_ROUTE, [
            'password' => '123456'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_INCORRECT_USERNAME,
        ]);
    }

    public function loginLecturer_ThrowsException_IfPasswordIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('login as lecturer with invalid password');
        $I->sendPOST(self::LOGIN_LECTURER_ROUTE, [
            'username' => 'zhangqinjie',
            'password' => 'zhangqinjie'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_INCORRECT_PASSWORD,
        ]);
    }

    public function loginLecturer_ThrowsException_IfNoPassword(FunctionalTester $I)
    {
        $I->wantTo('login as lecturer with no password');
        $I->sendPOST(self::LOGIN_LECTURER_ROUTE, [
            'username' => 'zhangqinjie'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_INCORRECT_PASSWORD,
        ]);
    }

    public function loginLecturer_ReturnsToken_IfValidUsernameAndPassword(FunctionalTester $I)
    {
        $I->wantTo('login as lecturer successfully');
        $I->sendPOST(self::LOGIN_LECTURER_ROUTE, [
            'username' => 'zhangqinjie',
            'password' => '123456'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'name' => 'string',
            'email' => 'string:email',
            'acad' => 'string',
            'token' => 'string'
        ]);
    }
}
