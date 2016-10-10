<?php

namespace common\models;

use Yii;

use common\components\Util;

/**
 * This is the model class for table "lesson".
 *
 * @property integer $id
 * @property string $semester
 * @property string $module_id
 * @property string $subject_area
 * @property string $catalog_number
 * @property string $class_section
 * @property string $component
 * @property string $facility
 * @property integer $venue_id
 * @property string $weekday
 * @property string $start_time
 * @property string $end_time
 * @property string $meeting_pattern
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Attendance[] $attendances
 * @property Venue $venue
 * @property Timetable[] $timetables
 */
class Lesson extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lesson';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['venue_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['semester', 'module_id', 'subject_area', 'catalog_number', 'start_time', 'end_time'], 'string', 'max' => 10],
            [['class_section', 'component', 'weekday', 'meeting_pattern'], 'string', 'max' => 5],
            [['facility'], 'string', 'max' => 15],
            [['venue_id'], 'exist', 'skipOnError' => true, 'targetClass' => Venue::className(), 'targetAttribute' => ['venue_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'semester' => 'Semester',
            'module_id' => 'Module ID',
            'subject_area' => 'Subject Area',
            'catalog_number' => 'Catalog Number',
            'class_section' => 'Class Section',
            'component' => 'Component',
            'facility' => 'Facility',
            'venue_id' => 'Venue ID',
            'weekday' => 'Weekday',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'meeting_pattern' => 'Meeting Pattern',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttendances()
    {
        return $this->hasMany(Attendance::className(), ['lesson_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVenue()
    {
        return $this->hasOne(Venue::className(), ['id' => 'venue_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTimetables()
    {
        return $this->hasMany(Timetable::className(), ['lesson_id' => 'id']);
    }
}
