<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "student".
 *
 * @property string $id
 * @property string $name
 * @property string $gender
 * @property string $acad
 * @property string $uuid
 * @property integer $user_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Attendance[] $attendances
 * @property User $user
 * @property Timetable[] $timetables
 */
class Student extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'student';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name'], 'required'],
            [['user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['id', 'acad'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 255],
            [['gender'], 'string', 'max' => 1],
            [['uuid'], 'string', 'max' => 40],
            [['user_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'gender' => 'Gender',
            'acad' => 'Acad',
            'uuid' => 'Uuid',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['updated_at'], $fields['created_at']);
        return $fields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttendances()
    {
        return $this->hasMany(Attendance::className(), ['student_id' => 'id']);
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
    public function getTimetables()
    {
        return $this->hasMany(Timetable::className(), ['student_id' => 'id']);
    }
}
