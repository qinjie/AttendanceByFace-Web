<?php

namespace api\common\models;

use api\common\models\User;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class Lecturer extends ActiveRecord
{
    public static function tableName()
    {
        return 'lecturer';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at']
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['updated_at'], $fields['created_at']);
        return $fields;
    }
}
