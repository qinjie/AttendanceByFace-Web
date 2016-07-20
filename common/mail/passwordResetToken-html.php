<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $token]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Reset Password</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width">
<style type="text/css">
  
  /* CLIENT-SPECIFIC STYLES ------------------- */
  
  

  /* RESET STYLES --------------------------- */
  
  

  /* MOBILE STYLES ------------------------ */
  
    
</style>
</head>
<body style="margin: 0; padding: 0;">

<!-- CONTAINER TABLE -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed;">
  <tr>
    <td align="center" style="padding: 20px 0 20px 0;">
      <!-- WRAPPER TABLE -->
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <!-- LOGO/PREHEADER TEXT -->
        <tr>
          <td style="border-bottom: 10px solid #00BCD4; padding: 0 20px 5px 20px">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="left" style="font-size: 20px; font-weight: bold;">
                  Attendance System
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<!-- CONTAINER TABLE -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed;">
  <tr>
    <td>
      <!-- WRAPPER TABLE -->
      <table border="0" cellpadding="0" cellspacing="0" width="600">
        <tr>
          <td style="padding: 0 0 20px 0">
            <table border="0" cellpadding="0" cellspacing="0"  width="100%" style="font-weight: bold; font-size: 20px">
              <tr>
                <td>
                  Hi, <?= Html::encode($user->username) ?>!
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<!-- CONTAINER TABLE -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed;">
  <tr>
    <td>
      <!-- WRAPPER TABLE -->
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td>
          	<p>
            	Follow the link below to reset your password:
            </p>
						
						<p>
						<?= Html::a(Html::encode('Reset Password'), $resetLink) ?>
						</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<!-- FOOTER -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed;">
  <tr>
    <td>
      <!-- WRAPPER TABLE -->
      <table border="0" cellpadding="0" cellspacing="0" width="600">
        <tr>
          <td style="padding: 30px 0 0 0">
            Delivered by <a href="http://www.np.edu.sg/">Ngee Ann Polytechnic</a>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

</body>
</html>
