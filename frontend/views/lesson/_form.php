<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Lesson */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="lesson-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'semester')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'module_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subject_area')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'catalog_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'class_section')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'component')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'facility')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'venue_id')->textInput() ?>

    <?= $form->field($model, 'weekday')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'start_time')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'end_time')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'meeting_pattern')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
