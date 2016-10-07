<?php
namespace api\models;


use common\models\User;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    const SCENARIO_STUDENT = 'student';
    const SCENARIO_LECTURER = 'lecturer';

    public $username;
    public $password;
    public $device_hash;

    private $_user;

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_STUDENT => ['username', 'password', 'device_hash'],
            self::SCENARIO_LECTURER => ['username', 'password'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password', 'device_hash'], 'required'],
            ['password', 'validatePassword'],
            ['device_hash', 'validateDevice']
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
                $this->addError($attribute, 'Incorrect username or password.');
        }
    }

    public function validateDevice($attribute, $params) {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if ($user && !$user->validateDevice($this->device_hash)) {
                $this->addError($attribute, 'Incorrect device.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            if ($user->status == User::STATUS_WAIT_EMAIL_DEVICE)
                $this->addError('status', User::CODE_UNVERIFIED_EMAIL_DEVICE);
            else if ($user->status == User::STATUS_WAIT_EMAIL)
                $this->addError('status', User::CODE_UNVERIFIED_EMAIL);
            else if ($user->status == User::STATUS_WAIT_DEVICE)
                $this->addError('status', User::CODE_UNVERIFIED_DEVICE);
            else if ($user->status == User::STATUS_ACTIVE) {
                return $user;
            } else $this->addError('status', User::CODE_INVALID_ACCOUNT);
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
            if (!$this->_user)
                $this->addError('username', 'No user with given username');
        }

        return $this->_user;
    }

    public function attributeLabels()
    {
        return [
        ];
    }
}
