<?php
namespace api\models;


use common\models\User;
use api\modules\v1\controllers\UserController;

use Yii;
use yii\base\Model;

/**
 * Register device form
 */
class RegisterDeviceForm extends Model
{
    public $username;
    public $password;
    public $device_hash;

    private $_user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password', 'device_hash'], 'required'],
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'string', 'min' => 4, 'max' => 255],
            ['password', 'validatePassword'],
            ['device_hash', 'required'],
            ['device_hash', 'unique', 'targetClass' => User::className(), 'message' => 'This device has already been taken.'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if ($user && !$user->validatePassword($this->password))
                $this->addError($attribute, 'Incorrect password.');
        }
    }

    public function registerDevice()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            if ($user->status == User::STATUS_BLOCKED || $user->status == User::STATUS_DELETED)
                $this->addError('status', UserController::CODE_INVALID_ACCOUNT);
            else {
                $user->device_hash = $this->device_hash;
                if ($user->status == User::STATUS_WAIT_EMAIL)
                    $user->status = User::STATUS_WAIT_EMAIL_DEVICE;
                else if ($user->status == User::STATUS_ACTIVE)
                    $user->status = User::STATUS_WAIT_DEVICE;
                if ($user->save()) return $user;
            }
        }
        if ($this->hasErrors()) return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
            if (!$this->_user || $this->_user->role != User::ROLE_STUDENT)
                $this->addError('username', 'No student with given username');
        }

        return $this->_user;
    }

    public function attributeLabels()
    {
        return [
        ];
    }
}
