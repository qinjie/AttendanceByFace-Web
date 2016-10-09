<?php
namespace tests\codeception\api;
use tests\codeception\api\FunctionalTester;

class LecturerCest
{
    private $accessToken;

    public function _before(FunctionalTester $I)
    {
        $this->accessToken = $I->loginLecturer()->token;
        $I->amBearerAuthenticated($this->accessToken);
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function getMyProfile(FunctionalTester $I)
    {
        $I->wantTo('get my lecturer profile');
        $I->sendGET('v1/lecturer/profile?expand=user');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'id' => 'string',
            'name' => 'string',
            'acad' => 'string',
            'email' => 'string:email',
            'user_id' => 'integer'
        ]);
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'email' => 'string:email'
        ], '$.user');
    }
}
