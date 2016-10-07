<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "venue_beacon".
 *
 * @property integer $id
 * @property integer $venue_id
 * @property integer $beacon_id
 * @property string $created_at
 *
 * @property Venue $venue
 * @property Beacon $beacon
 */
class VenueBeacon extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'venue_beacon';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['venue_id', 'beacon_id'], 'integer'],
            [['created_at'], 'safe'],
            [['venue_id'], 'exist', 'skipOnError' => true, 'targetClass' => Venue::className(), 'targetAttribute' => ['venue_id' => 'id']],
            [['beacon_id'], 'exist', 'skipOnError' => true, 'targetClass' => Beacon::className(), 'targetAttribute' => ['beacon_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'venue_id' => 'Venue ID',
            'beacon_id' => 'Beacon ID',
            'created_at' => 'Created At',
        ];
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
    public function getBeacon()
    {
        return $this->hasOne(Beacon::className(), ['id' => 'beacon_id']);
    }
}
