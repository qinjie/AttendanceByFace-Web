<?php

namespace common\models;

use common\components\Util;

use Yii;

/**
 * This is the model class for table "attendance".
 *
 * @property integer $id
 * @property string $student_id
 * @property integer $lesson_id
 * @property string $lecturer_id
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
 * @property Lecturer $lecturer
 */
class Attendance extends \yii\db\ActiveRecord
{
    const SEMESTER_START_DATE = '2016-10-03';
    const SEMESTER_END_DATE = '2016-10-16';

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
            [['student_id', 'lesson_id', 'lecturer_id'], 'required'],
            [['lesson_id', 'is_absent', 'is_late', 'late_min'], 'integer'],
            [['recorded_date', 'recorded_time', 'created_at', 'updated_at'], 'safe'],
            [['student_id', 'lecturer_id'], 'string', 'max' => 10],
            [['student_id', 'lesson_id', 'recorded_date'], 'unique', 'targetAttribute' => ['student_id', 'lesson_id', 'recorded_date'], 'message' => 'The combination of Student ID, Lesson ID and Recorded Date has already been taken.'],
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
            'recorded_date' => 'Recorded Date',
            'recorded_time' => 'Recorded Time',
            'is_absent' => 'Is Absent',
            'is_late' => 'Is Late',
            'late_min' => 'Late Minutes',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLecturer()
    {
        return $this->hasOne(Lecturer::className(), ['id' => 'lecturer_id']);
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
            // Cannot be absent and late at same time
            if ($this->is_absent && $this->is_late) return false;

            // If present (not absent, not late)
            if (!$this->is_absent && !$this->is_late) $this->late_min = 0;

            if (!$this->is_absent && $this->is_late) {
                // If late, you have to pass recorded_time
                if ($this->recorded_time)
                    $this->late_min = Util::getDifferenceInMinutes(
                        $this->lesson->start_time, $this->recorded_time);
                else return false;
            }
            return true;
        } else return false;
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();
        $extraFields['venue'] = function($model) {
            return $model->lesson->venue;
        };
        return $extraFields;
    }
}
