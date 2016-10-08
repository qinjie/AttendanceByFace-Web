<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use api\modules\v1\controllers\UserController;
use common\models\User;
use common\components\TokenHelper;

class SignupLecturerCest
{
    const SIGNUP_LECTURER_ROUTE = 'v1/lecturer/signup';
    const LOGIN_LECTURER_ROUTE = 'v1/lecturer/login';

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

    public function loginLecturer_ThrowsException_IfEmailNotActivated(FunctionalTester $I)
    {
        $I->wantTo('login as student when email is not activated');

        $I->sendPOST(self::LOGIN_LECTURER_ROUTE, [
            'username' => 'lecturer',
            'password' => '123456'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_UNVERIFIED_EMAIL
        ]);
    }

    public function activateEmail_Success(FunctionalTester $I)
    {
        $I->wantTo('activate email address successfully');
        $userId = $I->grabFromDatabase('user', 'id', [
            'username' => 'lecturer'
        ]);
        $token = $I->grabFromDatabase('user_token', 'token', [
            'user_id' => $userId,
            'action' => TokenHelper::TOKEN_ACTION_ACTIVATE_ACCOUNT
        ]);
        $I->sendGET('v1/user/confirm-email', [
            'token' => $token
        ]);
        $I->dontSeeInDatabase('user_token', [
            'user_id' => $userId,
            'action' => TokenHelper::TOKEN_ACTION_ACTIVATE_ACCOUNT
        ]);
        $I->seeInDatabase('user', [
            'username' => 'lecturer',
            'role' => User::ROLE_LECTURER,
            'status' => User::STATUS_ACTIVE
        ]);
    }

    public function loginLecturer_ReturnsToken_IfEmailIsActivated(FunctionalTester $I)
    {
        $I->wantTo('login as student successfully when email is activated');
        $I->sendPOST(self::LOGIN_LECTURER_ROUTE, [
            'username' => 'lecturer',
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
