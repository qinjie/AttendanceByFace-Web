<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_token".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $token
 * @property string $title
 * @property string $ip_address
 * @property string $expire_date
 * @property string $created_date
 * @property string $updated_date
 * @property integer $action
 *
 * @property User $user
 */
class UserToken extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'token', 'title', 'ip_address', 'expire_date', 'created_date', 'updated_date', 'action'], 'required'],
            [['user_id', 'action'], 'integer'],
            [['expire_date', 'created_date', 'updated_date'], 'safe'],
            [['token', 'title', 'ip_address'], 'string', 'max' => 255],
            [['token'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'token' => 'Token',
            'title' => 'Title',
            'ip_address' => 'Ip Address',
            'expire_date' => 'Expire Date',
            'created_date' => 'Created Date',
            'updated_date' => 'Updated Date',
            'action' => 'Action',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
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
