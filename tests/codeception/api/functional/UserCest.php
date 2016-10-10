<?php
namespace tests\codeception\api;


use tests\codeception\api\FunctionalTester;
use common\components\TokenHelper;

class UserCest
{
    private $accessToken;

    public function _before(FunctionalTester $I)
    {
        $this->accessToken = $I->loginStudent()->token;
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function getPersonId(FunctionalTester $I)
    {
        $I->wantTo('get person id');
        $I->amBearerAuthenticated($this->accessToken);
        $I->sendGET('v1/user/mine?fields=person_id');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'person_id' => 'string'
        ]);
    }

    public function getFaceId(FunctionalTester $I)
    {
        $I->wantTo('get face id');
        $I->amBearerAuthenticated($this->accessToken);
        $I->sendGET('v1/user/mine?fields=face_id');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'face_id' => 'array'
        ]);
    }

    public function cannotGetPasswordHashAndAuthKey(FunctionalTester $I)
    {
        $I->wantTo('prevent getting password hash and auth key');
        $I->amBearerAuthenticated($this->accessToken);
        $I->sendGET('v1/user/mine?fields=password_hash,auth_key');
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('[]');
    }

    public function updatePersonId(FunctionalTester $I)
    {
        $I->wantTo('update person id');
        $I->amBearerAuthenticated($this->accessToken);
        $I->sendPOST('v1/user/mine', [
            'person_id' => 'new-person-id'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'username' => 'string',
            'email' => 'string:email',
            'person_id' => 'string',
            'face_id' => 'array'
        ]);
    }

    public function updateFaceId(FunctionalTester $I)
    {
        $I->wantTo('update face id');
        $I->amBearerAuthenticated($this->accessToken);
        $I->sendPOST('v1/user/mine', [
            'face_id' => [
                'face-id-1',
                'face-id-2'
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'username' => 'string',
            'email' => 'string:email',
            'person_id' => 'string',
            'face_id' => 'array'
        ]);
    }

    public function logout(FunctionalTester $I)
    {
        $I->wantTo('logout');
        $I->amBearerAuthenticated($this->accessToken);
        $I->sendPOST('v1/user/logout');
        $I->seeResponseCodeIs(200);
    }

    public function changePassword(FunctionalTester $I)
    {
        $I->wantTo('change password');
        $I->amBearerAuthenticated($this->accessToken);
        $I->sendPOST('v1/user/change-password', [
            'oldPassword' => '123456',
            'newPassword' => '654321'
        ]);
        $I->seeResponseCodeIs(200);

        // Revert to original password
        $I->sendPOST('v1/user/change-password', [
            'oldPassword' => '654321',
            'newPassword' => '123456'
        ]);
    }

    public function resetPassword(FunctionalTester $I)
    {
        $I->wantTo('reset password');
        $I->sendPOST('v1/user/reset-password', [
            'email' => 'canh@mail.com'
        ]);
        $I->seeResponseCodeIs(200);
        $userId = $I->grabFromDatabase('user', 'id', [
            'email' => 'canh@mail.com'
        ]);
        $I->seeInDatabase('user_token', [
            'user_id' => $userId,
            'action' => TokenHelper::TOKEN_ACTION_RESET_PASSWORD
        ]);
    }
}