<?php
namespace tests\codeception\api;

use common\models\User;
/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

   /**
    * Define custom actions here
    */

    public function loginStudent($user = null) {
        $I = $this;
        $deviceHash = $I->grabFromDatabase('user', 'device_hash', [
            'username' => 'canhnht',
            'role' => User::ROLE_STUDENT
        ]);
        if (!$user) $user = [
            'username' => 'canhnht',
            'password' => '123456',
            'device_hash' => $deviceHash
        ];
        $I->sendPOST('v1/student/login', $user);
        return json_decode($I->grabResponse());
    }

    public function loginLecturer($user = null) {
        $I = $this;
        if (!$user) $user = [
            'username' => 'zhangqinjie',
            'password' => '123456'
        ];
        $I->sendPOST('v1/lecturer/login', $user);
        return json_decode($I->grabResponse());
    }
}
