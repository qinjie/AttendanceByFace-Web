<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use api\modules\v1\controllers\UserController;
use common\models\User;

class SignupLecturerCest
{
    const SIGNUP_LECTURER_ROUTE = 'v1/lecturer/signup';

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function signupLecturer_ThrowsException_IfUsernameIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('signup as lecturer with invalid username');
        $I->sendPOST(self::SIGNUP_LECTURER_ROUTE, [
            'username' => 'lec',
            'password' => '123456',
            'email' => 'lecturer@mail.com'
        ]);
        $I->seeResponseCodeIs(400);
        $I->dontSeeInDatabase('user', [
            'username' => 'lec',
            'role' => User::ROLE_LECTURER,
            'status' => User::STATUS_WAIT_EMAIL
        ]);
        $I->seeInDatabase('lecturer', [
            'email' => 'lecturer@mail.com',
            'user_id' => null
        ]);
    }

    public function signupLecturer_ThrowsException_IfPasswordIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('signup as lecturer with invalid password');
        $I->sendPOST(self::SIGNUP_LECTURER_ROUTE, [
            'username' => 'lecturer',
            'password' => '12345',
            'email' => 'lecturer@mail.com'
        ]);
        $I->seeResponseCodeIs(400);
        $I->dontSeeInDatabase('user', [
            'username' => 'lecturer',
            'role' => User::ROLE_LECTURER,
            'status' => User::STATUS_WAIT_EMAIL
        ]);
        $I->seeInDatabase('lecturer', [
            'email' => 'lecturer@mail.com',
            'user_id' => null
        ]);
    }

    public function signupLecturer_ThrowsException_IfEmailIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('signup as lecturer with invalid email');
        $I->sendPOST(self::SIGNUP_LECTURER_ROUTE, [
            'username' => 'lecturer',
            'password' => '123456',
            'email' => 'lecturer'
        ]);
        $I->seeResponseCodeIs(400);
        $I->dontSeeInDatabase('user', [
            'username' => 'lecturer',
            'role' => User::ROLE_LECTURER,
            'status' => User::STATUS_WAIT_EMAIL
        ]);
        $I->seeInDatabase('lecturer', [
            'email' => 'lecturer@mail.com',
            'user_id' => null
        ]);
    }

    public function signupLecturer_ReturnsToken_IfSuccess(FunctionalTester $I)
    {
        $I->wantTo('signup as lecturer successfully');
        $I->sendPOST(self::SIGNUP_LECTURER_ROUTE, [
            'username' => 'lecturer',
            'password' => '123456',
            'email' => 'lecturer@mail.com'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'token' => 'string'
        ]);
        $I->seeInDatabase('user', [
            'username' => 'lecturer',
            'role' => User::ROLE_LECTURER,
            'status' => User::STATUS_WAIT_EMAIL
        ]);
        $userId = $I->grabFromDatabase('user', 'id', [
            'username' => 'lecturer'
        ]);
        $I->seeInDatabase('lecturer', [
            'email' => 'lecturer@mail.com',
            'user_id' => $userId
        ]);
    }
}
