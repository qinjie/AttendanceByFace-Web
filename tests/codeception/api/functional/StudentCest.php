<?php
namespace tests\codeception\api;
use tests\codeception\api\FunctionalTester;

class StudentCest
{
    private $accessToken;

    public function _before(FunctionalTester $I)
    {
        $this->accessToken = $I->loginStudent()->token;
        $I->amBearerAuthenticated($this->accessToken);
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function getStudentProfile(FunctionalTester $I)
    {
        $I->wantTo('get my student profile');
        $I->sendGET('v1/student/profile?expand=user');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'id' => 'string',
            'name' => 'string',
            'gender' => 'null|string',
            'acad' => 'string',
            'uuid' => 'null|string',
            'user_id' => 'integer'
        ]);
        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'email' => 'string:email'
        ], '$.user');
    }
}
