<?php
/**
 * Created by PhpStorm.
 * User: zqi2
 * Date: 24/5/2015
 * Time: 6:05 PM
 */
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$confirmLink = Yii::$app->urlManager->createAbsoluteUrl(['site/confirm-email', 'token' => $user->email_confirm_token]);
?>

Hi, <?= Html::encode($user->username) ?>!

Follow the link below to confirm your email address:

<?= Html::a(Html::encode($confirmLink), $confirmLink) ?>

If you have not registered on our website, then simply delete this email.