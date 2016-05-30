<?php

namespace api\modules\v1\models;

use api\common\models\User;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class Venue extends ActiveRecord
{

    public static function tableName()
    {
        return 'venue';
    }

    public function fields() {
        $fields = parent::fields();
        unset($fields['created_at'], $fields['updated_at']);
        return $fields;
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }
}
