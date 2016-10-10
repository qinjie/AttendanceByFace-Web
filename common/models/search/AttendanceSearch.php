<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Attendance;

/**
 * AttendanceSearch represents the model behind the search form about `common\models\Attendance`.
 */
class AttendanceSearch extends Attendance
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'lesson_id', 'is_absent', 'is_late', 'late_min'], 'integer'],
            [['student_id', 'recorded_date', 'recorded_time', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Attendance::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, Yii::$app->id=='app-api' ? '' : null);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'lesson_id' => $this->lesson_id,
            'recorded_time' => $this->recorded_time,
            'is_absent' => $this->is_absent,
            'is_late' => $this->is_late,
            'late_min' => $this->late_min,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        if ($this->student_id)
            $query->andWhere(['student_id' => $this->student_id]);
        if ($this->recorded_date)
            $query->andWhere(['recorded_date' => $this->recorded_date]);

        return $dataProvider;
    }
}
