<?php
namespace api\common\models;

use api\common\models\User;
use Yii;
use yii\base\Model;


class LecturerLoginModel extends Model
{
    public $username;
    public $password;
    private $_user;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        $user = $this->getUser();
        if (!$this->errors && !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Incorrect password.');
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
        if (!$this->_user) {
            $this->_user = User::findByUsername($this->username);
            if (!$this->_user)
                $this->addError('username', 'No user with given username');
        }
        return $this->_user;
    }
}
