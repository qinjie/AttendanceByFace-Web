<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Lesson;

/**
 * LessonSearch represents the model behind the search form about `common\models\Lesson`.
 */
class LessonSearch extends Lesson
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'venue_id'], 'integer'],
            [['semester', 'module_id', 'subject_area', 'catalog_number', 'class_section', 'component', 'facility', 'weekday', 'start_time', 'end_time', 'meeting_pattern', 'created_at', 'updated_at'], 'safe'],
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
        $query = Lesson::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'venue_id' => $this->venue_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'semester', $this->semester])
            ->andFilterWhere(['like', 'module_id', $this->module_id])
            ->andFilterWhere(['like', 'subject_area', $this->subject_area])
            ->andFilterWhere(['like', 'catalog_number', $this->catalog_number])
            ->andFilterWhere(['like', 'class_section', $this->class_section])
            ->andFilterWhere(['like', 'component', $this->component])
            ->andFilterWhere(['like', 'facility', $this->facility])
            ->andFilterWhere(['like', 'weekday', $this->weekday])
            ->andFilterWhere(['like', 'start_time', $this->start_time])
            ->andFilterWhere(['like', 'end_time', $this->end_time]);

        if ($this->meeting_pattern)
            $query->andWhere(['or',
                ['meeting_pattern' => $this->meeting_pattern],
                ['meeting_pattern' => '']
            ]);

        return $dataProvider;
    }
}
