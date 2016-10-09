<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use api\modules\v1\controllers\UserController;
use common\models\User;

class RegisterDeviceCest
{
    const REGISTER_DEVICE_ROUTE = 'v1/student/register-device';

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function registerDevice_ThrowsException_IfUsernameIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('register device with invalid username');
        $I->sendPOST(self::REGISTER_DEVICE_ROUTE, [
            'username' => 'canh',
            'password' => '123456',
            'device_hash' => '11:11:11:11:11:11'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_INCORRECT_USERNAME,
        ]);
    }

    public function registerDevice_ThrowsException_IfPasswordIsInvalid(FunctionalTester $I)
    {
        $I->wantTo('register device with invalid password');
        $I->sendPOST(self::REGISTER_DEVICE_ROUTE, [
            'username' => 'canhnht',
            'password' => '111111',
            'device_hash' => '11:11:11:11:11:11'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_INCORRECT_PASSWORD,
        ]);
    }

    public function registerDevice_ThrowsException_IfNoDevice(FunctionalTester $I)
    {
        $I->wantTo('register device with no device');
        $I->sendPOST(self::REGISTER_DEVICE_ROUTE, [
            'username' => 'canhnht',
            'password' => '123456'
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_DUPLICATE_DEVICE,
        ]);
    }

    public function registerDevice_ThrowsException_IfDeviceDuplicate(FunctionalTester $I)
    {
        $I->wantTo('register device with duplicate device');
        $deviceHash = $I->grabFromDatabase('user', 'device_hash', [
            'username' => 'charity',
            'role' => User::ROLE_STUDENT
        ]);
        $I->sendPOST(self::REGISTER_DEVICE_ROUTE, [
            'username' => 'canhnht',
            'password' => '123456',
            'device_hash' => $deviceHash
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'code' => UserController::CODE_DUPLICATE_DEVICE,
        ]);
    }

    public function registerDevice_ReturnsToken_IfValidData(FunctionalTester $I)
    {
        $I->wantTo('register device successfully');
        $I->sendPOST(self::REGISTER_DEVICE_ROUTE, [
            'username' => 'canhnht',
            'password' => '123456',
            'device_hash' => '11:11:11:11:11:11'
        ]);
        $I->seeResponseCodeIs(200);
    }
}
