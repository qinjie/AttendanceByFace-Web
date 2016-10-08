<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use api\modules\v1\controllers\UserController;
use common\models\User;

class SignupStudentCest
{
    const SIGNUP_STUDENT_ROUTE = 'v1/student/signup';

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function signupStudent_ThrowsException_IfUsernameIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('signup as student with invalid username');
        $I->sendPOST(self::SIGNUP_STUDENT_ROUTE, [
            'username' => 'stu',
            'password' => '123456',
            'student_id' => '55555555B',
            'device_hash' => '11:11:11:11:11:11',
            'email' => 'student@mail.com'
        ]);
        $I->seeResponseCodeIs(400);
        $I->dontSeeInDatabase('user', [
            'username' => 'stu',
            'role' => User::ROLE_STUDENT,
            'status' => User::STATUS_WAIT_EMAIL_DEVICE
        ]);
        $I->seeInDatabase('student', [
            'id' => '55555555B',
            'user_id' => null
        ]);
    }

    public function signupStudent_ThrowsException_IfPasswordIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('signup as student with invalid password');
        $I->sendPOST(self::SIGNUP_STUDENT_ROUTE, [
            'username' => 'student',
            'password' => '12345',
            'student_id' => '55555555B',
            'device_hash' => '11:11:11:11:11:11',
            'email' => 'student@mail.com'
        ]);
        $I->seeResponseCodeIs(400);
        $I->dontSeeInDatabase('user', [
            'username' => 'student',
            'role' => User::ROLE_STUDENT,
            'status' => User::STATUS_WAIT_EMAIL_DEVICE
        ]);
        $I->seeInDatabase('student', [
            'id' => '55555555B',
            'user_id' => null
        ]);
    }

    public function signupStudent_ThrowsException_IfStudentIdIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('signup as student with invalid student id');
        $I->sendPOST(self::SIGNUP_STUDENT_ROUTE, [
            'username' => 'student',
            'password' => '123456',
            'student_id' => '555555555',
            'device_hash' => '11:11:11:11:11:11',
            'email' => 'student@mail.com'
        ]);
        $I->seeResponseCodeIs(400);
        $I->dontSeeInDatabase('user', [
            'username' => 'student',
            'role' => User::ROLE_STUDENT,
            'status' => User::STATUS_WAIT_EMAIL_DEVICE
        ]);
        $I->seeInDatabase('student', [
            'id' => '55555555B',
            'user_id' => null
        ]);
    }

    public function signupStudent_ThrowsException_IfEmailIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('signup as student with invalid email');
        $I->sendPOST(self::SIGNUP_STUDENT_ROUTE, [
            'username' => 'student',
            'password' => '123456',
            'student_id' => '55555555B',
            'device_hash' => '11:11:11:11:11:11',
            'email' => 'student'
        ]);
        $I->seeResponseCodeIs(400);
        $I->dontSeeInDatabase('user', [
            'username' => 'student',
            'role' => User::ROLE_STUDENT,
            'status' => User::STATUS_WAIT_EMAIL_DEVICE
        ]);
        $I->seeInDatabase('student', [
            'id' => '55555555B',
            'user_id' => null
        ]);
    }

    public function signupStudent_ThrowsException_IfNoDeviceHash(FunctionalTester $I)
    {
        $I->wantTo('signup as student with no device hash');
        $I->sendPOST(self::SIGNUP_STUDENT_ROUTE, [
            'username' => 'student',
            'password' => '123456',
            'student_id' => '55555555B',
            'email' => 'student@mail.com'
        ]);
        $I->seeResponseCodeIs(400);
        $I->dontSeeInDatabase('user', [
            'username' => 'student',
            'role' => User::ROLE_STUDENT,
            'status' => User::STATUS_WAIT_EMAIL_DEVICE
        ]);
        $I->seeInDatabase('student', [
            'id' => '55555555B',
            'user_id' => null
        ]);
    }

    public function signupStudent_ReturnsToken_IfSuccess(FunctionalTester $I)
    {
        $I->wantTo('signup as student successfully');
        $I->sendPOST(self::SIGNUP_STUDENT_ROUTE, [
            'username' => 'student',
            'password' => '123456',
            'student_id' => '55555555B',
            'device_hash' => '11:11:11:11:11:11',
            'email' => 'student@mail.com'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'token' => 'string'
        ]);
        $I->seeInDatabase('user', [
            'username' => 'student',
            'role' => User::ROLE_STUDENT,
            'status' => User::STATUS_WAIT_EMAIL_DEVICE
        ]);
        $userId = $I->grabFromDatabase('user', 'id', [
            'username' => 'student'
        ]);
        $I->seeInDatabase('student', [
            'id' => '55555555B',
            'user_id' => $userId
        ]);
    }
}
