<?php
namespace api\common\models;

use api\common\models\User;
use Yii;
use yii\base\Model;


class PasswordResetModel extends Model
{
    public $email;


    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => 'api\common\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'There is no user with such email.'
            ],
        ];
    }

    public function sendEmail()
    {
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $this->email,
        ]);

        if (!$user) {
            return false;
        }

        $randomPassword = Yii::$app->security->generateRandomString(6);
        $user->setPassword($randomPassword);
        if ($user->save()) {
            Yii::$app
                ->mailer
                ->compose(
                    ['html' => '@common/mail/passwordResetToken-html'],
                    [
                        'user' => $user,
                        'newPassword' => $randomPassword
                    ]
                )
                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                ->setTo($this->email)
                ->setSubject('Password reset for ' . Yii::$app->name)
                ->send();
            return $user;
        }
        return null;
    }
}
