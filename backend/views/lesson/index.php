<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Lessons';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lesson-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            'semester',
            'module_id',
            // 'subject_area',
            // 'catalog_number',
            'class_section',
            'component',
            // 'facility',
            // 'venue_id',
            'weekday',
            'start_time',
            'end_time',
            // 'meeting_pattern',
            // 'created_at',
            // 'updated_at',
            'venue.name',
            'venue.location',

            [
                'class' => 'yii\grid\ActionColumn',
                'visibleButtons' => [
                    'delete' => false
                ]
            ],
        ],
    ]); ?>
</div>
