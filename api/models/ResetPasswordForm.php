<?php
namespace api\models;


use common\models\User;
use common\models\UserToken;
use api\modules\v1\controllers\UserController;
use common\components\TokenHelper;

use Yii;
use yii\base\Model;

/**
 * Reset password form
 */
class ResetPasswordForm extends Model
{
    public $email;

    private $_user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => 'api\common\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'No user with given email'
            ],
        ];
    }

    public function resetPassword()
    {
        $user = User::find()->where([
                'and',
                ['or', ['status' => User::STATUS_ACTIVE], ['status' => User::STATUS_WAIT_DEVICE]],
                ['email' => $this->email],
            ])->one();

        if (!$user) {
            return false;
        }

        UserToken::deleteAll(['user_id' => $user->id, 'action' => TokenHelper::TOKEN_ACTION_RESET_PASSWORD]);
        $userToken = TokenHelper::createUserToken($user->id, TokenHelper::TOKEN_ACTION_RESET_PASSWORD);

        if ($user->save()) {
            Yii::$app->mailer->compose(['html' => '@common/mail/passwordResetToken-html'],
                    [
                        'user' => $user,
                        'token' => $userToken->token,
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

    public function attributeLabels()
    {
        return [
        ];
    }
}
