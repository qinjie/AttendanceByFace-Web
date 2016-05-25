<?php

namespace api\modules\v1\models;

use api\common\models\User;
use api\modules\v1\models\Lesson;
use api\common\models\Student;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class Timetable extends ActiveRecord
{
    public static function tableName()
    {
        return 'timetable';
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
}
