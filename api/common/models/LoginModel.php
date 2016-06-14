<?php
namespace api\common\models;

use api\common\models\User;
use Yii;
use yii\base\Model;


class LoginModel extends Model
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
            ['device_hash', 'validateDevice'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->errors) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    public function validateDevice($attribute, $params) {
        if (!$this->errors) {
            $user = $this->getUser();
            if (!$user || !$user->validateDevice($this->device_hash)) {
                $this->addError($attribute, 'Incorrect device.');
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            return $this->_user;
        } else {
            return false;
        }
    }

    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }
        return $this->_user;
    }
}
