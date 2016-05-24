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

$confirmLink = Yii::$app->urlManager->createAbsoluteUrl(['index.php/v1/user/confirm-email', 'token' => $token]);
?>

Hi, <?= Html::encode($user->username) ?>!

Follow the link below to confirm your email address:

<?= Html::a(Html::encode('Confirm Your Email'), $confirmLink) ?>

If you have not registered on our website, then simply delete this email.