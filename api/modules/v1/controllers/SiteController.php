<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;

class SiteController extends Controller {

    public function actionIndex() {
        return 'Welcome to Attendance Taking BLE';
    }
}
