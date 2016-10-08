<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$confirmLink = Yii::$app->urlManager->createAbsoluteUrl(['site/confirm-email', 'token' => $user->email_confirm_token]);
?>
Hello <?= $user->username ?>,

Follow the link below to confirm your email address:

<?= $confirmLink ?>

If you have not registered on our website, then simply delete this email.