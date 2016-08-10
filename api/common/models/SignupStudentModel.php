<?php

namespace api\common\models;

use api\common\models\User;
use api\common\models\Student;
use api\common\helpers\TokenHelper;
use yii\base\Model;
use Yii;

class SignupStudentModel extends Model
{
    public $username;
    public $email;
    public $student_id;
    public $password;
    public $role;
    public $device_hash;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => 'api\common\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 4, 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            // ['email', 'match', 'pattern' => '/^s[0-9]{8}@connect.np.edu.sg$/'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => 'api\common\models\User', 'message' => 'This email address has already been taken.'],

            ['student_id', 'filter', 'filter' => 'trim'],
            ['student_id', 'required'],
            ['student_id', 'validateStudentId'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            
            ['device_hash', 'required'],
            ['device_hash', 'string', 'max' => 255],
            ['device_hash', 'unique', 'targetClass' => 'api\common\models\User', 'message' => 'This device has already been used.']
        ];
    }

    public function validateStudentId($attribute, $params) {
        $student = Student::findOne(['id' => $this->student_id]);
        if (!$this->errors && !$student) {
            $this->addError($attribute, 'No student with given student id');
        }
    }

    public function signup()
    {
        if ($this->validate()) {
            $student = Student::findOne(['id' => $this->student_id]);
            if ($student && !$student->user_id) {
                $user = new User();
                $user->username = $this->username;
                $user->email = $this->email;
                $user->setPassword($this->password);
                $user->generateAuthKey();
                $user->status = User::STATUS_WAIT_EMAIL_DEVICE;
                $user->role = $this->role;
                $user->name = User::$roles[$this->role];
                $user->device_hash = $this->device_hash;

                if ($user->save()) {
                    $student->user_id = $user->id;
                    if ($student->save()) {
                        $token = TokenHelper::createUserToken($user->id, TokenHelper::TOKEN_ACTION_ACTIVATE_ACCOUNT);
                        # send activation email
                        Yii::$app->mailer->compose(['html' => '@common/mail/emailConfirmToken-html'], ['user' => $user, 'token' => $token->token])
                            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
                            ->setTo($this->email)
                            ->setSubject('Email confirmation for ' . Yii::$app->name)
                            ->send();

                        return $user;
                    }
                }
            } else {
                $this->addError('student', 'Student already registered');
            }
        }

        return null;
    }
}
