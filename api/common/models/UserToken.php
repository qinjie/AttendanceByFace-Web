<?php

namespace api\common\models;

use api\common\models\User;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class UserToken extends ActiveRecord
{
    public static function tableName()
    {
        return 'as_user_token';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => null,
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
            [['expire', 'created_at'], 'safe'],
            [['token', 'ip_address'], 'string', 'max' => 32],
            [['token'], 'unique']
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
}
