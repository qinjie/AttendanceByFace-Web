<?php

use yii\helpers\Html;
use yii\grid\GridView;
use moonland\phpexcel\Excel;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Attendances';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="attendance-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?=
        Excel::widget([
            'models' => $dataProvider->getModels(),
            'mode' => 'export',
            // 'columns' => ['id', 'recorded_date', 'recorded_time'],
            // 'headers' => [
            //     'id' => 'Attendance ID',
            //     'recorded_date' => 'Recorded date',
            //     'recorded_time' => 'Recorded time'
            // ]
        ]);
        ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'recorded_date',
            'lesson.class_section',
            'lesson.component',
            'lesson.start_time',
            'lesson.end_time',
            'student.name',
            // 'id',
            // 'student_id',
            // 'lesson_id',
            // 'lecturer_id',
            // 'recorded_time',
            'is_absent',
            'is_late',
            'late_min',
            // 'created_at',
            // 'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
