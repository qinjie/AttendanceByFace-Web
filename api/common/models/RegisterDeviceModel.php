<?php
namespace api\common\models;

use api\common\models\User;
use Yii;
use yii\base\Model;


class RegisterDeviceModel extends Model
{
    public $username;
    public $password;
    public $device_hash;
    private $_user;

    public function rules()
    {
        return [
            [['username', 'password', 'device_hash'], 'required'],
            ['password', 'validatePassword'],
            ['device_hash', 'unique', 'targetClass' => 'api\common\models\User', 'message' => 'This device has already been taken.'],
            ['device_hash', 'validateDevice'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        $user = $this->getUser();
        if (!$this->errors && !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Incorrect password.');
        }
    }

    public function validateDevice($attribute, $params) {
        $user = $this->getUser();
        if (!$this->errors && $user->device_hash == $this->device_hash) {
            $this->addError($attribute, 'Already registered device.');
        }
    }

    public function registerDevice()
    {
        if ($this->validate()) {
            return $this->_user;
        } else {
            return false;
        }
    }

    protected function getUser()
    {
        if (!$this->_user) {
            $this->_user = User::findByUsername($this->username);
            if (!$this->_user)
                $this->addError('username', 'No user with given username');
        }
        return $this->_user;
    }
}
