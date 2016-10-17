<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "beacon".
 *
 * @property string $id
 * @property string $uuid
 * @property string $major
 * @property string $minor
 * @property integer $user_id
 * @property string $created_at
 * @property string $updated_at
 */
class Beacon extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'beacon';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'uuid', 'major', 'minor'], 'required'],
            [['user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['id', 'major', 'minor'], 'string', 'max' => 10],
            [['uuid'], 'string', 'max' => 100],
            [['uuid', 'major', 'minor'], 'unique', 'targetAttribute' => ['uuid', 'major', 'minor'], 'message' => 'The combination of Uuid, Major and Minor has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uuid' => 'Uuid',
            'major' => 'Major',
            'minor' => 'Minor',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
