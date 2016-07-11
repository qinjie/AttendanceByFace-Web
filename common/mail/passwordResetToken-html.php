<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

?>
<div class="password-reset">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p>
    	Please log in using this new password: <strong><?= Html::encode($newPassword) ?></strong>
    </p>
</div>
