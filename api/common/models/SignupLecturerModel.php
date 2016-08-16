<?php

namespace api\common\models;

use api\common\models\User;
use api\common\models\Lecturer;
use api\common\helpers\TokenHelper;
use yii\base\Model;
use Yii;

class SignupLecturerModel extends Model
{
    public $username;
    public $email;
    public $password;
    public $role;

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
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => 'api\common\models\User', 'message' => 'This email address has already been taken.'],
            ['email', 'exist', 'targetClass' => 'api\common\models\Lecturer', 'message' => 'No lecturer with given email.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6]
        ];
    }

    public function signup()
    {
        if ($this->validate()) {
            $lecturer = Lecturer::findOne(['email' => $this->email]);
            if ($lecturer) {
                $user = new User();
                $user->username = $this->username;
                $user->email = $this->email;
                $user->setPassword($this->password);
                $user->generateAuthKey();
                $user->status = User::STATUS_WAIT_EMAIL;
                $user->role = $this->role;
                $user->name = User::$roles[$this->role];                

                if ($user->save()) {
                    $lecturer->user_id = $user->id;
                    if ($lecturer->save()) {
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
                $this->addError('lecturer', 'No lecturer with given email');
            }
        }

        return null;
    }
}
