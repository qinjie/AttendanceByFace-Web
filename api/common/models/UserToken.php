<?php

namespace api\common\models;

use api\common\models\User;
use api\common\helpers\TokenHelper;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class UserToken extends ActiveRecord
{
    public static function tableName()
    {
        return 'user_token';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_date', 'updated_date'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_date'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['expire_date', 'created_date'], 'safe'],
            [['token', 'ip_address'], 'string', 'max' => 32],
            [['token'], 'unique'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->refresh();
        parent::afterSave($insert, $changedAttributes);
    }

    public function getIsActive()
    {
        // check whether token has expired.
        $current = time();
        $expire = strtotime($this->expire);
        if ($expire > $current)
            return true;
        else
            return false;
    }

    public static function removeEmailConfirmToken($userId) {
        return static::deleteAll(['user_id' => $userId, 'action' => TokenHelper::TOKEN_ACTION_ACTIVATE_ACCOUNT]);
    }

    public static function removeResetPasswordToken($userId) {
        return static::deleteAll(['user_id' => $userId, 'action' => TokenHelper::TOKEN_ACTION_RESET_PASSWORD]);   
    }
}
