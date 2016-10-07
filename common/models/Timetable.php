<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "timetable".
 *
 * @property integer $id
 * @property string $student_id
 * @property integer $lesson_id
 * @property string $lecturer_id
 * @property string $created_at
 *
 * @property Student $student
 * @property Lesson $lesson
 * @property Lecturer $lecturer
 */
class Timetable extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'timetable';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['student_id', 'lesson_id', 'lecturer_id'], 'required'],
            [['lesson_id'], 'integer'],
            [['created_at'], 'safe'],
            [['student_id', 'lecturer_id'], 'string', 'max' => 10],
            [['student_id', 'lesson_id', 'lecturer_id'], 'unique', 'targetAttribute' => ['student_id', 'lesson_id', 'lecturer_id'], 'message' => 'The combination of Student ID, Lesson ID and Lecturer ID has already been taken.'],
            [['student_id'], 'exist', 'skipOnError' => true, 'targetClass' => Student::className(), 'targetAttribute' => ['student_id' => 'id']],
            [['lesson_id'], 'exist', 'skipOnError' => true, 'targetClass' => Lesson::className(), 'targetAttribute' => ['lesson_id' => 'id']],
            [['lecturer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Lecturer::className(), 'targetAttribute' => ['lecturer_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'student_id' => 'Student ID',
            'lesson_id' => 'Lesson ID',
            'lecturer_id' => 'Lecturer ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(Student::className(), ['id' => 'student_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLesson()
    {
        return $this->hasOne(Lesson::className(), ['id' => 'lesson_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLecturer()
    {
        return $this->hasOne(Lecturer::className(), ['id' => 'lecturer_id']);
    }
}
