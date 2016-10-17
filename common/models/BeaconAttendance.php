<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "beacon_attendance".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $scanned_user_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 * @property User $scannedUser
 */
class BeaconAttendance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'beacon_attendance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'scanned_user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['scanned_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['scanned_user_id' => 'id']],
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
            'scanned_user_id' => 'Scanned User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScannedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'scanned_user_id']);
    }
}
