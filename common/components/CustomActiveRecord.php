<?php

namespace common\components;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\Linkable;

class CustomActiveRecord extends ActiveRecord
{

    public function afterSave($insert, $changedAttributes)
    {
        $this->refresh();
        parent::afterSave($insert, $changedAttributes);
    }

}
