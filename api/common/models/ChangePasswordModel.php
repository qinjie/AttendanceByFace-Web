<?php
namespace api\common\models;

use api\common\models\User;
use Yii;
use yii\base\Model;


class ChangePasswordModel extends Model
{
    public $oldPassword;
    public $newPassword;
    public $user;

    public function rules()
    {
        return [
            ['oldPassword', 'required'],
            ['oldPassword', 'validateOldPassword'],
            ['newPassword', 'required'],
            ['newPassword', 'string', 'min' => 6],
        ];
    }

    public function validateOldPassword($attribute, $params)
    {
        $user = $this->user;
        // return $this->addError($attribute, json_encode($user));
        if (!$this->errors && !$user->validatePassword($this->oldPassword)) {
            $this->addError($attribute, 'Incorrect password.');
        }
    }

    public function changePassword() {
        if ($this->validate()) {
            $user = $this->user;
            $user->setPassword($this->newPassword);
            return $user->save();
        }
        return null;
    }
}
