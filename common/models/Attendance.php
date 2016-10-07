<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "attendance".
 *
 * @property integer $id
 * @property string $student_id
 * @property integer $lesson_id
 * @property string $recorded_date
 * @property string $recorded_time
 * @property integer $is_absent
 * @property integer $is_late
 * @property integer $late_min
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Student $student
 * @property Lesson $lesson
 */
class Attendance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'attendance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['student_id', 'lesson_id'], 'required'],
            [['lesson_id', 'is_absent', 'is_late', 'late_min'], 'integer'],
            [['recorded_date', 'recorded_time', 'created_at', 'updated_at'], 'safe'],
            [['student_id'], 'string', 'max' => 10],
            [['student_id'], 'exist', 'skipOnError' => true, 'targetClass' => Student::className(), 'targetAttribute' => ['student_id' => 'id']],
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
            'student_id' => 'Student ID',
            'lesson_id' => 'Lesson ID',
            'recorded_date' => 'Recorded Date',
            'recorded_time' => 'Recorded Time',
            'is_absent' => 'Is Absent',
            'is_late' => 'Is Late',
            'late_min' => 'Late Min',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
}
