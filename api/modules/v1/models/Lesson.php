<?php

namespace api\modules\v1\models;

use api\common\models\User;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class Lesson extends ActiveRecord
{
    public $component;

    public static function tableName()
    {
        return 'lesson';
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

    public function getComponent(){
        $query = self::find()->where(['id' => $this->id])->one();
        return $query['component'];
    }
}
