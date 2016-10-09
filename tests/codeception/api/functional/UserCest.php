<?php
namespace tests\codeception\api;
use tests\codeception\api\FunctionalTester;

class UserCest
{
    private $accessToken;

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function getPersonId(FunctionalTester $I)
    {
        $I->wantTo('get person id');
        $this->accessToken = $I->loginStudent()->token;
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
            'face_id' => 'string'
        ]);
    }
}
