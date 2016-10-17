<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "beacon".
 *
 * @property integer $id
 * @property string $uuid
 * @property integer $major
 * @property integer $minor
 * @property integer $user_id
 * @property integer $lesson_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 * @property Lesson $lesson
 */
class Beacon extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'beacon';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uuid', 'major', 'minor'], 'required'],
            [['major', 'minor', 'user_id', 'lesson_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['uuid'], 'string', 'max' => 100],
            [['uuid', 'major', 'minor'], 'unique', 'targetAttribute' => ['uuid', 'major', 'minor'], 'message' => 'The combination of Uuid, Major and Minor has already been taken.'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['lesson_id'], 'exist', 'skipOnError' => true, 'targetClass' => Lesson::className(), 'targetAttribute' => ['lesson_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uuid' => 'Uuid',
            'major' => 'Major',
            'minor' => 'Minor',
            'user_id' => 'User ID',
            'lesson_id' => 'Lesson ID',
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
    public function getLesson()
    {
        return $this->hasOne(Lesson::className(), ['id' => 'lesson_id']);
    }
}
