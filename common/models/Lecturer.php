<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "lecturer".
 *
 * @property string $id
 * @property string $name
 * @property string $acad
 * @property string $email
 * @property integer $user_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Timetable[] $timetables
 */
class Lecturer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lecturer';
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
            [['name', 'email'], 'string', 'max' => 255],
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
            'acad' => 'Acad',
            'email' => 'Email',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimetables()
    {
        return $this->hasMany(Timetable::className(), ['lecturer_id' => 'id']);
    }
}
